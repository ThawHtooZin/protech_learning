<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\AssignmentAttachment;
use App\Models\AssignmentSubmission;
use App\Models\AssignmentSubmissionFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AssignmentFileService
{
    public function disk(): string
    {
        return (string) config('lms.assignments.disk', 'local');
    }

    public function maxFiles(): int
    {
        return (int) config('lms.assignments.max_files', 5);
    }

    public function maxFileKb(): int
    {
        return (int) config('lms.assignments.max_file_kb', 20480);
    }

    /**
     * Any file type is allowed; only size is validated.
     *
     * @return list<string>
     */
    public function fileRules(): array
    {
        return ['file', 'max:'.$this->maxFileKb()];
    }

    /**
     * @param  list<UploadedFile>  $files
     * @return list<AssignmentAttachment>
     */
    public function storeAttachments(Assignment $assignment, array $files): array
    {
        $this->assertFileCount($files);

        $stored = [];
        foreach ($files as $file) {
            $stored[] = $this->storeOneAttachment($assignment, $file);
        }

        return $stored;
    }

    public function storeOneAttachment(Assignment $assignment, UploadedFile $file): AssignmentAttachment
    {
        $path = $file->store(
            "assignments/{$assignment->id}/resources",
            $this->disk(),
        );

        return $assignment->attachments()->create([
            'original_name' => $file->getClientOriginalName(),
            'disk' => $this->disk(),
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize() ?: 0,
        ]);
    }

    /**
     * @param  list<UploadedFile>  $files
     * @return list<AssignmentSubmissionFile>
     */
    public function replaceSubmissionFiles(AssignmentSubmission $submission, array $files): array
    {
        $this->assertFileCount($files);

        foreach ($submission->files as $existing) {
            Storage::disk($existing->disk)->delete($existing->path);
            $existing->delete();
        }

        $stored = [];
        foreach ($files as $file) {
            $path = $file->store(
                "assignments/{$submission->assignment_id}/submissions/{$submission->id}",
                $this->disk(),
            );

            $stored[] = $submission->files()->create([
                'original_name' => $file->getClientOriginalName(),
                'disk' => $this->disk(),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize() ?: 0,
            ]);
        }

        return $stored;
    }

    public function deleteAssignmentFiles(Assignment $assignment): void
    {
        $assignment->loadMissing(['attachments', 'submissions.files']);

        foreach ($assignment->attachments as $attachment) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }

        foreach ($assignment->submissions as $submission) {
            foreach ($submission->files as $file) {
                Storage::disk($file->disk)->delete($file->path);
            }
        }

        Storage::disk($this->disk())->deleteDirectory("assignments/{$assignment->id}");
    }

    /**
     * @param  list<UploadedFile>  $files
     */
    private function assertFileCount(array $files): void
    {
        if (count($files) < 1 || count($files) > $this->maxFiles()) {
            throw ValidationException::withMessages([
                'files' => __('You may upload between 1 and :max files.', ['max' => $this->maxFiles()]),
            ]);
        }
    }
}
