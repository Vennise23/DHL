<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentStatusHistory;
use App\Models\RpaLog;
use Illuminate\Support\Facades\DB;
use App\Services\AIService;
use App\Services\AIProcessingService;
use App\Models\IncidentAttachment;
use Illuminate\Support\Facades\Storage;

class IncidentService
{
    public function __construct(
        private AIService $aiService,
        private AIProcessingService $aiProcessingService
    ) {}
    public function create($request, $userId)
    {
        DB::beginTransaction();

        try {

            // 1. Create incident
            $incident = Incident::create([
                'title' => $request['title'],
                'description' => $request['description'],
                'status' => 'draft',
                'priority' => $request['priority'] ?? 'medium',
                'source' => $request['source'] ?? 'manual',
                'category' => $request['category'] ?? null,
                'content_hash' => hash('sha256', $request['title'] . ' ' . $request['description']),
                'assigned_to' => $request['assigned_to'] ?? null,
                'created_by' => $userId,
            ]);

            // 2. Save attachments (IMPORTANT)
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {

                    $path = $file->store('incidents', 'public');

                    IncidentAttachment::create([
                        'incident_id' => $incident->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                    ]);
                }
            }

            // 3. AI input detection
            $hasImage = !empty($request['attachments']);
            $inputType = $hasImage ? 'image' : 'text';

            $request['ai_input_type'] = $inputType;

            // 4. Run AI
            $aiResult = $this->aiService->analyze($request);

            // 4.5 Compare + update incident
            $updatedFields = [];

            if (!empty($aiResult['category']) && $aiResult['category'] !== $incident->category) {
                $updatedFields['category'] = $aiResult['category'];
            }

            if (!empty($aiResult['priority']) && $aiResult['priority'] !== $incident->priority) {
                $updatedFields['priority'] = $aiResult['priority'];
            }

            // Apply update if needed
            if (!empty($updatedFields)) {
                $incident->update($updatedFields);

                // optional: log change
                IncidentStatusHistory::create([
                    'incident_id' => $incident->id,
                    'status' => $incident->status,
                    'changed_by' => $userId,
                    'note' => 'AI updated: ' . json_encode($updatedFields),
                ]);
            }

            // 5. Save AI Processing
            app(AIProcessingService::class)->store(
                $incident->id,
                $aiResult,
                $request['title'] . ' ' . ($request['description'] ?? ''),
                json_encode($aiResult),
                $inputType
            );

            // 6. history
            IncidentStatusHistory::create([
                'incident_id' => $incident->id,
                'status' => 'draft',
                'changed_by' => $userId,
                'note' => 'Incident created',
            ]);

            // 7. rpa log
            RpaLog::create([
                'incident_id' => $incident->id,
                'source_type' => $incident->source,
                'action' => 'create_incident',
                'status' => 'success',
                'message' => 'Incident created successfully',
                'file_hash' => hash('sha256', 'sample log content'),
                'log_file_path' => '/logs/rpa_' . $incident->source . '_' . str_pad($incident->id, 3, '0', STR_PAD_LEFT) . '.txt',
                'screenshot_path' => '/screenshots/rpa_' . $incident->source . '_' . str_pad($incident->id, 3, '0', STR_PAD_LEFT) . '.png',
                'external_source_id' => $incident->source === 'email' ? 'email-' . $incident->id : ($incident->source === 'telegram' ? 'telegram-' . $incident->id : null),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return $incident;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public  function update($request, $id, $userId)
    {
        DB::beginTransaction();

        try {
            // Check if incident exists
            $incident = Incident::findOrFail($id);

            // Check changes
            $titleChanged = $incident->title !== $request->title;
            $descriptionChanged = $incident->description !== $request->description;
            $notes = [];

            // Handle attachments changes
            $existingIds = $request->existing_attachments ?? [];
            $deletedAttachments = $incident->attachments->whereNotIn('id', $existingIds);
            $attachmentDeleted = $deletedAttachments->count() > 0;
            $attachmentAdded = false;
            if ($request->hasFile('attachments')) {

                $attachmentAdded = true;

                foreach (
                    $request->file('attachments')
                    as $file
                ) {

                    $path = $file->store(
                        'incidents',
                        'public'
                    );

                    IncidentAttachment::create([
                        'incident_id' => $incident->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                    ]);
                }
            }

            foreach ($deletedAttachments as $attachment) {

                Storage::disk('public')
                    ->delete($attachment->file_path);

                $attachment->delete();
            }

            // 1. Update incident
            $incident->update([
                'title' => $request['title'],
                'description' => $request['description'],
                'status' => $request['status'],
                'priority' => $request['priority'],
                'source' => $request['source'],
                'category' => $request['category'],
                'assigned_to' => $request['assigned_to'],
                'updated_by' => $userId,
            ]);

            if ($incident->wasChanged()) {

                $changes = array_keys(
                    $incident->getChanges()
                );

                $notes[] =
                    'Fields updated: ' .
                    implode(', ', $changes);
            }

            // 2. Check run AI?
            $shouldRunAI =
                $titleChanged ||
                $descriptionChanged ||
                $attachmentAdded ||
                $attachmentDeleted;

            // 3. Run AI again if needed
            if ($shouldRunAI) {

                $notes[] =
                    'AI reprocessed';

                $aiResult =
                    $this->aiService->analyze($request);

                if (!empty($aiResult)) {

                    $notes[] =
                        'AI Category: ' .
                        ($aiResult['category'] ?? '-');

                    $notes[] =
                        'AI Priority: ' .
                        ($aiResult['priority'] ?? '-');
                }

                // update incident from AI
                $incident->update([

                    'category' =>
                    $aiResult['category']
                        ?? $incident->category,

                    'priority' =>
                    $aiResult['priority']
                        ?? $incident->priority,
                ]);

                // update AI processing
                $incident->aiProcessing()
                    ->updateOrCreate(
                        [
                            'incident_id' => $incident->id
                        ],
                        [
                            'ai_summary' =>
                            $aiResult['summary'] ?? null,

                            'ai_tags' =>
                            json_encode(
                                $aiResult['tags'] ?? []
                            ),

                            'ai_suggestions' =>
                            $aiResult['suggestions'] ?? null,

                            'conflict_flag' =>
                            $aiResult['conflict'] ?? false,

                            'ai_confidence' =>
                            $aiResult['confidence'] ?? null,

                            'ai_input_type' => ($attachmentAdded ||
                                $attachmentDeleted)
                                ? 'image'
                                : 'text',
                        ]
                    );
            }


            // 4. Add History
            $note = count($notes)
                ? implode(' | ', $notes)
                : 'No changes';
            IncidentStatusHistory::create([
                'incident_id' => $incident->id,
                'status' => $incident->status,
                'changed_by' => $userId,
                'note' => $note,
            ]);;

            DB::commit();

            return $incident;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {

            $incident = Incident::with([
                'attachments',
                'histories',
                'rpaLogs',
                'aiProcessing',
            ])->findOrFail($id);

            // 1. Delete physical files
            foreach ($incident->attachments as $attachment) {

                if (
                    $attachment->file_path &&
                    Storage::disk('public')->exists($attachment->file_path)
                ) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
            }

            // 2. Delete related tables
            $incident->attachments()->delete();

            $incident->histories()->delete();

            $incident->rpaLogs()->delete();

            $incident->aiProcessing()->delete();

            // 3. Delete incident
            $incident->delete();

            DB::commit();

            return true;
        } catch (\Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }

    public function reviewerUpdate($request, $id, $userId)
    {
        DB::beginTransaction();

        try {
            $incident = Incident::findOrFail($id);

            $incident->update([
                'status' => $request['status'] ?? $incident->status,
                'assigned_to' => $request['assigned_to'] ?? $incident->assigned_to,
            ]);

            // Add history
            IncidentStatusHistory::create([
                'incident_id' => $incident->id,
                'status' => $incident->status,
                'changed_by' => $userId,
                'note' => $request['note'] ?? 'Reviewer updated status and assignment',
            ]);

            DB::commit();

            return $incident;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function createFromRPA($request, $userId, $type = 'email')
    {
        DB::beginTransaction();

        try {

            // 1. Create incident
            $incident = Incident::create([
                'title' => $request['title'],
                'description' => $request['description'],
                'status' => 'draft',
                'priority' => $request['priority'] ?? 'medium',
                'source' => 'email',
                'category' => $request['category'] ?? 'Email Incident',
                'assigned_to' => null,
                'created_by' => $userId,
            ]);

            // 2. Save attachments
            if ($request->hasFile('attachments')) {

                foreach ($request->file('attachments') as $file) {

                    $path = $file->store('incidents', 'public');

                    IncidentAttachment::create([
                        'incident_id' => $incident->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                    ]);
                }
            }

            // 3. Detect AI input type
            $hasImage = $request->hasFile('attachments');

            $inputType = $hasImage ? 'image' : 'text';

            $request['ai_input_type'] = $inputType;

            // 4. Run AI
            $aiResult = $this->aiService->analyze($request);

            // 5. Update AI fields
            $updatedFields = [];

            if (
                !empty($aiResult['category']) &&
                $aiResult['category'] !== $incident->category
            ) {
                $updatedFields['category'] = $aiResult['category'];
            }

            if (
                !empty($aiResult['priority']) &&
                $aiResult['priority'] !== $incident->priority
            ) {
                $updatedFields['priority'] = $aiResult['priority'];
            }

            if (!empty($updatedFields)) {

                $incident->update($updatedFields);

                IncidentStatusHistory::create([
                    'incident_id' => $incident->id,
                    'status' => $incident->status,
                    'changed_by' => $userId,
                    'note' => 'AI updated: ' . json_encode($updatedFields),
                ]);
            }

            // 6. Save AI processing
            app(AIProcessingService::class)->store(
                $incident->id,
                $aiResult,
                $request['title'] . ' ' . ($request['description'] ?? ''),
                json_encode($aiResult),
                $inputType
            );

            // 7. History
            IncidentStatusHistory::create([
                'incident_id' => $incident->id,
                'status' => 'draft',
                'changed_by' => $userId,
                'note' => 'Incident created from RPA ' . $type . ' ingestion',
            ]);

            // 8. RPA log
            RpaLog::create([
                'incident_id' => $incident->id,
                'source_type' => $type,
                'action' => 'create_incident',
                'status' => 'success',
                'message' => 'Incident created successfully from RPA ' . $type,
                'file_hash' => hash('sha256', 'sample log content'),
                'log_file_path' => '/logs/rpa_' . $type . '_' . str_pad($incident->id, 3, '0', STR_PAD_LEFT) . '.txt',
                'screenshot_path' => '/screenshots/rpa_' . $type . '_' . str_pad($incident->id, 3, '0', STR_PAD_LEFT) . '.png',
                'external_source_id' => $type === 'email' ? 'email-' . $incident->id : ($type === 'telegram' ? 'telegram-' . $incident->id : null),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return $incident;
        } catch (\Exception $e) {

            DB::rollBack();

            throw $e;
        }
    }

    public function checkDuplicateFromRPA($request)
    {
        $hash = $request->input('hash');

        $exists = Incident::where('content_hash', $hash)->exists();

        $duplicatedIncidentId = Incident::where(
            'content_hash',
            $hash
        )->value('id');

        if ($exists) {
            $this->addDuplicateRPA($duplicatedIncidentId);
        }

        return response()->json([
            'is_duplicate' => $exists
        ]);
    }

    public function addDuplicateRPA($request)
    {
        $incidentId = Incident::where('content_hash', $request->hash)->value('id');
        $log_file_path = $request->log_file_path;

        RpaLog::create([
            'incident_id' => $incidentId,
            'source_type' => 'rpa',
            'action' => 'duplicate_skipped',
            'status' => 'success',
            'message' => 'Duplicate incident detected and skipped by RPA',
            'file_hash' => $request->hash_file ?? hash('sha256', 'sample log content'),
            'log_file_path' => $log_file_path ?? ('/logs/rpa_duplicate_' . str_pad($incidentId ?? 0, 3, '0', STR_PAD_LEFT) . '.txt'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return true;
    }

    public function logRPAFailure($request)
    {
        $log_file_path = $request->log_file_path;

        RpaLog::create([
            'source_type' => 'rpa',
            'action' => 'failed',
            'status' => 'failed',
            'message' => $request->message ?? 'RPA processing failed',
            'file_hash' => $request->hash_file ?? hash('sha256', 'sample log content'),
            'log_file_path' => $log_file_path ?? ('/logs/rpa_failure_' . now()->format('Ymd_His') . '.txt'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}
