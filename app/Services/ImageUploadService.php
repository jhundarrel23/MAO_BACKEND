<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Exception;

class ImageUploadService
{
    /**
     * Allowed image types
     */
    private const ALLOWED_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    
    /**
     * Maximum file size in KB
     */
    private const MAX_FILE_SIZE = 5120; // 5MB
    
    /**
     * Image resize dimensions
     */
    private const RESIZE_DIMENSIONS = [
        'profile' => ['width' => 300, 'height' => 300],
        'document' => ['width' => 800, 'height' => 600],
        'farm' => ['width' => 1024, 'height' => 768],
        'program' => ['width' => 1200, 'height' => 800],
        'inventory' => ['width' => 600, 'height' => 600],
    ];

    /**
     * Upload and process image
     *
     * @param UploadedFile $file
     * @param string $type (profile, document, farm, program, inventory)
     * @param string|null $oldPath
     * @return string|null
     */
    public function uploadImage(UploadedFile $file, string $type, ?string $oldPath = null): ?string
    {
        try {
            // Validate file
            if (!$this->validateFile($file)) {
                throw new Exception('Invalid file uploaded');
            }

            // Generate unique filename
            $filename = $this->generateFilename($file);
            
            // Determine storage disk based on type
            $disk = $this->getDiskByType($type);
            
            // Create subdirectory based on current date
            $directory = $this->getDirectory($type);
            
            // Full path for storage
            $path = $directory . '/' . $filename;
            
            // Process and resize image if needed
            $processedImage = $this->processImage($file, $type);
            
            // Store the image
            Storage::disk($disk)->put($path, $processedImage);
            
            // Delete old image if provided
            if ($oldPath && Storage::disk($disk)->exists($oldPath)) {
                Storage::disk($disk)->delete($oldPath);
            }
            
            return $path;
            
        } catch (Exception $e) {
            \Log::error('Image upload failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Upload multiple images
     *
     * @param array $files
     * @param string $type
     * @return array
     */
    public function uploadMultipleImages(array $files, string $type): array
    {
        $uploadedPaths = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $path = $this->uploadImage($file, $type);
                if ($path) {
                    $uploadedPaths[] = $path;
                }
            }
        }
        
        return $uploadedPaths;
    }

    /**
     * Delete image
     *
     * @param string $path
     * @param string $type
     * @return bool
     */
    public function deleteImage(string $path, string $type): bool
    {
        try {
            $disk = $this->getDiskByType($type);
            
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->delete($path);
            }
            
            return true;
        } catch (Exception $e) {
            \Log::error('Image deletion failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get image URL
     *
     * @param string|null $path
     * @param string $type
     * @return string|null
     */
    public function getImageUrl(?string $path, string $type): ?string
    {
        if (!$path) {
            return null;
        }
        
        $disk = $this->getDiskByType($type);
        
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->url($path);
        }
        
        return null;
    }

    /**
     * Validate uploaded file
     *
     * @param UploadedFile $file
     * @return bool
     */
    private function validateFile(UploadedFile $file): bool
    {
        // Check if file is valid
        if (!$file->isValid()) {
            return false;
        }
        
        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE * 1024) {
            return false;
        }
        
        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_TYPES)) {
            return false;
        }
        
        // Check MIME type
        $mimeType = $file->getMimeType();
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes)) {
            return false;
        }
        
        return true;
    }

    /**
     * Generate unique filename
     *
     * @param UploadedFile $file
     * @return string
     */
    private function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return time() . '_' . Str::random(10) . '.' . $extension;
    }

    /**
     * Get storage disk by type
     *
     * @param string $type
     * @return string
     */
    private function getDiskByType(string $type): string
    {
        return match($type) {
            'profile', 'document', 'signature' => 'beneficiaries',
            'farm', 'parcel', 'ownership' => 'farms',
            'program', 'banner' => 'programs',
            'inventory', 'item' => 'inventory',
            'rsbsa', 'supporting' => 'documents',
            default => 'public',
        };
    }

    /**
     * Get directory by type
     *
     * @param string $type
     * @return string
     */
    private function getDirectory(string $type): string
    {
        $date = date('Y/m');
        
        return match($type) {
            'profile' => "profiles/{$date}",
            'document', 'signature' => "documents/{$date}",
            'farm', 'parcel' => "farms/{$date}",
            'ownership' => "ownership/{$date}",
            'program', 'banner' => "programs/{$date}",
            'inventory', 'item' => "items/{$date}",
            'rsbsa', 'supporting' => "rsbsa/{$date}",
            default => "misc/{$date}",
        };
    }

    /**
     * Process and resize image
     *
     * @param UploadedFile $file
     * @param string $type
     * @return string
     */
    private function processImage(UploadedFile $file, string $type): string
    {
        // If Intervention Image is not available, return original file content
        if (!class_exists(Image::class)) {
            return file_get_contents($file->getPathname());
        }
        
        try {
            $image = Image::make($file->getPathname());
            
            // Get resize dimensions for the type
            $dimensions = $this->getResizeDimensions($type);
            
            if ($dimensions) {
                $image->resize($dimensions['width'], $dimensions['height'], function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }
            
            // Optimize image quality
            $image->encode($file->getClientOriginalExtension(), 85);
            
            return $image->__toString();
            
        } catch (Exception $e) {
            // If image processing fails, return original
            return file_get_contents($file->getPathname());
        }
    }

    /**
     * Get resize dimensions by type
     *
     * @param string $type
     * @return array|null
     */
    private function getResizeDimensions(string $type): ?array
    {
        return match($type) {
            'profile', 'signature' => self::RESIZE_DIMENSIONS['profile'],
            'document', 'ownership', 'rsbsa', 'supporting' => self::RESIZE_DIMENSIONS['document'],
            'farm', 'parcel' => self::RESIZE_DIMENSIONS['farm'],
            'program', 'banner' => self::RESIZE_DIMENSIONS['program'],
            'inventory', 'item' => self::RESIZE_DIMENSIONS['inventory'],
            default => null,
        };
    }
}