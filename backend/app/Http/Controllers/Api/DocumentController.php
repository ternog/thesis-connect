<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Document;
use App\Models\Thesis;
use App\Models\ThesisDownload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function testUpload(Request $request)
    {
        // Simple test endpoint to isolate finfo issues
        try {
            if (!isset($_FILES['document'])) {
                return response()->json(['error' => 'No file uploaded'], 400);
            }

            $uploadedFile = $_FILES['document'];
            
            return response()->json([
                'success' => true,
                'file_info' => [
                    'name' => $uploadedFile['name'],
                    'size' => $uploadedFile['size'],
                    'type' => $uploadedFile['type'],
                    'tmp_name' => $uploadedFile['tmp_name'],
                    'error' => $uploadedFile['error']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Exception: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function store(Request $request, Thesis $thesis)
    {
        if (!$request->user()->canUploadTheses() && 
            $thesis->uploaded_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Skip all Laravel validation and use pure PHP
        try {
            if (!isset($_FILES['document'])) {
                return response()->json([
                    'message' => 'Please select a document to upload.'
                ], 422);
            }

            $uploadedFile = $_FILES['document'];
            if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
                return response()->json([
                    'message' => 'File upload failed. Please try again.'
                ], 422);
            }

            // Check file size (10MB = 10485760 bytes)
            if ($uploadedFile['size'] > 10485760) {
                return response()->json([
                    'message' => 'The file size must not exceed 10MB.'
                ], 422);
            }

            // Get file extension from original name
            $originalName = $uploadedFile['name'];
            $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            
            // Validate file extension
            if ($fileExtension !== 'pdf') {
                return response()->json([
                    'message' => 'The document must be a PDF file.'
                ], 422);
            }
            
            // Additional PDF validation by checking file signature
            $tempPath = $uploadedFile['tmp_name'];
            $fileContent = file_get_contents($tempPath);
            if (substr($fileContent, 0, 4) !== '%PDF') {
                return response()->json([
                    'message' => 'The uploaded file is not a valid PDF document.'
                ], 422);
            }
            
            $fileHash = hash_file('sha256', $tempPath);

            // Check for duplicate file
            $existingDocument = Document::where('file_hash', $fileHash)->first();
            if ($existingDocument) {
                return response()->json([
                    'message' => 'This document has already been uploaded.'
                ], 422);
            }

            // Deactivate previous documents for this thesis
            $thesis->documents()->update(['is_active' => false]);

            // Generate unique filename
            $filename = Str::uuid() . '.pdf';
            $filePath = 'documents/' . $filename;

            // Store file manually to avoid any MIME type detection
            $destinationPath = storage_path('app/public/documents');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            
            $fullPath = $destinationPath . '/' . $filename;
            if (!move_uploaded_file($tempPath, $fullPath)) {
                return response()->json([
                    'message' => 'Failed to store the uploaded file.'
                ], 500);
            }

            // Create document record
            $document = Document::create([
                'thesis_id' => $thesis->id,
                'original_name' => $originalName,
                'file_path' => $filePath,
                'file_hash' => $fileHash,
                'file_size' => $uploadedFile['size'],
                'mime_type' => 'application/pdf', // Hardcoded since we only allow PDFs
                'version' => $thesis->documents()->max('version') + 1,
                'uploaded_by' => $request->user()->id,
            ]);

            // Log activity
            ActivityLog::logActivity(
                "Uploaded document for thesis: {$thesis->title}",
                $document,
                $request->user(),
                ['file_name' => $originalName, 'file_size' => $uploadedFile['size']],
                'document_uploaded',
                'document'
            );

            return response()->json($document->load('uploader'), 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download(Request $request, Document $document)
    {
        $thesis = $document->thesis;

        // Check if user can access this document
        if ($thesis->status !== 'approved' && 
            !$request->user()?->canApproveTheses() && 
            $thesis->uploaded_by !== $request->user()?->id) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        if (!$document->exists()) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        // Increment download count
        $thesis->incrementDownloadCount();

        // Record download
        ThesisDownload::recordDownload($thesis, $document, $request->user());

        // Log activity
        if ($request->user()) {
            ActivityLog::logActivity(
                "Downloaded document: {$document->original_name} from thesis: {$thesis->title}",
                $document,
                $request->user(),
                ['thesis_id' => $thesis->id],
                'document_downloaded',
                'document'
            );
        }

        // Return file download using pure PHP
        $fullPath = storage_path('app/public/' . $document->file_path);
        
        return response()->download($fullPath, $document->original_name, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function view(Request $request, Document $document)
    {
        $thesis = $document->thesis;

        // Check if user can access this document
        if ($thesis->status !== 'approved' && 
            !$request->user()?->canApproveTheses() && 
            $thesis->uploaded_by !== $request->user()?->id) {
            return response()->json(['message' => 'Document not found.'], 404);
        }

        if (!$document->exists()) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        // Return file for inline viewing using pure PHP
        $fullPath = storage_path('app/public/' . $document->file_path);
        
        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $document->original_name . '"',
        ]);
    }

    public function destroy(Request $request, Document $document)
    {
        $thesis = $document->thesis;

        if (!$request->user()->canApproveTheses() && 
            $thesis->uploaded_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $document->delete();

        return response()->json(['message' => 'Document deleted successfully.']);
    }

    public function versions(Thesis $thesis)
    {
        $documents = $thesis->documents()
            ->with('uploader')
            ->orderBy('version', 'desc')
            ->get();

        return response()->json($documents);
    }

}