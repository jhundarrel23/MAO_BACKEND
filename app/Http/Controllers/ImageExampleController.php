<?php

namespace App\Http\Controllers;

use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ImageExampleController extends Controller
{
    private ImageUploadService $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    /**
     * Upload beneficiary profile photo
     */
    public function uploadBeneficiaryPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'government_id_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'signature_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $uploadedFiles = [];

        // Upload profile photo
        if ($request->hasFile('profile_photo')) {
            $path = $this->imageUploadService->uploadImage(
                $request->file('profile_photo'),
                'profile'
            );
            if ($path) {
                $uploadedFiles['profile_photo'] = $path;
                $uploadedFiles['profile_photo_url'] = $this->imageUploadService->getImageUrl($path, 'profile');
            }
        }

        // Upload government ID photo
        if ($request->hasFile('government_id_photo')) {
            $path = $this->imageUploadService->uploadImage(
                $request->file('government_id_photo'),
                'document'
            );
            if ($path) {
                $uploadedFiles['government_id_photo'] = $path;
                $uploadedFiles['government_id_photo_url'] = $this->imageUploadService->getImageUrl($path, 'document');
            }
        }

        // Upload signature photo
        if ($request->hasFile('signature_photo')) {
            $path = $this->imageUploadService->uploadImage(
                $request->file('signature_photo'),
                'signature'
            );
            if ($path) {
                $uploadedFiles['signature_photo'] = $path;
                $uploadedFiles['signature_photo_url'] = $this->imageUploadService->getImageUrl($path, 'signature');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Images uploaded successfully',
            'data' => $uploadedFiles
        ]);
    }

    /**
     * Upload farm parcel images
     */
    public function uploadFarmImages(Request $request): JsonResponse
    {
        $request->validate([
            'ownership_document' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'farm_location_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'farm_sketch_map' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $uploadedFiles = [];

        // Upload ownership document
        if ($request->hasFile('ownership_document')) {
            $path = $this->imageUploadService->uploadImage(
                $request->file('ownership_document'),
                'ownership'
            );
            if ($path) {
                $uploadedFiles['ownership_document_photo'] = $path;
                $uploadedFiles['ownership_document_url'] = $this->imageUploadService->getImageUrl($path, 'ownership');
            }
        }

        // Upload farm location photo
        if ($request->hasFile('farm_location_photo')) {
            $path = $this->imageUploadService->uploadImage(
                $request->file('farm_location_photo'),
                'farm'
            );
            if ($path) {
                $uploadedFiles['farm_location_photo'] = $path;
                $uploadedFiles['farm_location_photo_url'] = $this->imageUploadService->getImageUrl($path, 'farm');
            }
        }

        // Upload farm sketch map
        if ($request->hasFile('farm_sketch_map')) {
            $path = $this->imageUploadService->uploadImage(
                $request->file('farm_sketch_map'),
                'farm'
            );
            if ($path) {
                $uploadedFiles['farm_sketch_map'] = $path;
                $uploadedFiles['farm_sketch_map_url'] = $this->imageUploadService->getImageUrl($path, 'farm');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Farm images uploaded successfully',
            'data' => $uploadedFiles
        ]);
    }

    /**
     * Upload program images (multiple)
     */
    public function uploadProgramImages(Request $request): JsonResponse
    {
        $request->validate([
            'program_banner' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'program_photos' => 'nullable|array',
            'program_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $uploadedFiles = [];

        // Upload program banner
        if ($request->hasFile('program_banner')) {
            $path = $this->imageUploadService->uploadImage(
                $request->file('program_banner'),
                'banner'
            );
            if ($path) {
                $uploadedFiles['program_banner'] = $path;
                $uploadedFiles['program_banner_url'] = $this->imageUploadService->getImageUrl($path, 'banner');
            }
        }

        // Upload multiple program photos
        if ($request->hasFile('program_photos')) {
            $programPhotos = $this->imageUploadService->uploadMultipleImages(
                $request->file('program_photos'),
                'program'
            );
            
            if (!empty($programPhotos)) {
                $uploadedFiles['program_photos'] = $programPhotos;
                $uploadedFiles['program_photos_urls'] = array_map(function($path) {
                    return $this->imageUploadService->getImageUrl($path, 'program');
                }, $programPhotos);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Program images uploaded successfully',
            'data' => $uploadedFiles
        ]);
    }

    /**
     * Upload inventory item photo
     */
    public function uploadInventoryImage(Request $request): JsonResponse
    {
        $request->validate([
            'item_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $uploadedFiles = [];

        if ($request->hasFile('item_photo')) {
            $path = $this->imageUploadService->uploadImage(
                $request->file('item_photo'),
                'item'
            );
            if ($path) {
                $uploadedFiles['item_photo'] = $path;
                $uploadedFiles['item_photo_url'] = $this->imageUploadService->getImageUrl($path, 'item');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Inventory item image uploaded successfully',
            'data' => $uploadedFiles
        ]);
    }

    /**
     * Upload distribution proof images
     */
    public function uploadDistributionImages(Request $request): JsonResponse
    {
        $request->validate([
            'distribution_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'beneficiary_signature' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $uploadedFiles = [];

        // Upload distribution photo
        if ($request->hasFile('distribution_photo')) {
            $path = $this->imageUploadService->uploadImage(
                $request->file('distribution_photo'),
                'program'
            );
            if ($path) {
                $uploadedFiles['distribution_photo'] = $path;
                $uploadedFiles['distribution_photo_url'] = $this->imageUploadService->getImageUrl($path, 'program');
            }
        }

        // Upload beneficiary signature
        if ($request->hasFile('beneficiary_signature')) {
            $path = $this->imageUploadService->uploadImage(
                $request->file('beneficiary_signature'),
                'signature'
            );
            if ($path) {
                $uploadedFiles['beneficiary_signature'] = $path;
                $uploadedFiles['beneficiary_signature_url'] = $this->imageUploadService->getImageUrl($path, 'signature');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Distribution images uploaded successfully',
            'data' => $uploadedFiles
        ]);
    }

    /**
     * Delete image
     */
    public function deleteImage(Request $request): JsonResponse
    {
        $request->validate([
            'image_path' => 'required|string',
            'image_type' => 'required|string|in:profile,document,signature,farm,ownership,program,banner,item'
        ]);

        $deleted = $this->imageUploadService->deleteImage(
            $request->input('image_path'),
            $request->input('image_type')
        );

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete image'
        ], 500);
    }
}