<?php

namespace App\Http\Controllers;

use App\Http\Requests\RSBSAEnrollmentRequest;
use App\Models\User;
use App\Models\BeneficiaryProfile;
use App\Models\RSBSAEnrollment;
use App\Models\RSBSACropProduction;
use App\Models\RSBSALivestockProduction;
use App\Models\RSBSAAquacultureProduction;
use App\Models\RSBSADocumentRequirement;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RSBSAController extends Controller
{
    private ImageUploadService $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    /**
     * Display RSBSA enrollment form
     */
    public function showEnrollmentForm()
    {
        return view('rsbsa.enrollment-form');
    }

    /**
     * Store RSBSA enrollment
     */
    public function store(Request $request): JsonResponse
    {
        // Validate the comprehensive RSBSA form
        $validatedData = $request->validate([
            // Personal Information
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'extension_name' => 'nullable|string|max:50',
            'birth_date' => 'required|date|before:today',
            'sex' => 'required|in:male,female',
            'civil_status' => 'required|string',
            'contact_number' => 'required|string|max:20',
            
            // Address
            'barangay' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'region' => 'required|string|max:10',
            
            // Household Information
            'total_household_members' => 'required|integer|min:1',
            'household_members_working_farm' => 'required|integer|min:0',
            'annual_family_income' => 'nullable|numeric|min:0',
            'income_classification' => 'nullable|in:below_poverty,low_income,middle_income',
            
            // Farming Information
            'years_farming_experience' => 'nullable|integer|min:0|max:100',
            'main_livelihood' => 'required|in:farming,fishing,farm_labor,agri_business,mixed',
            
            // Education and Training
            'highest_education' => 'nullable|in:None,Pre-school,Elementary,Junior High School,Senior High School,Vocational,College,Post Graduate',
            'attended_agricultural_training' => 'boolean',
            'training_programs_attended' => 'nullable|string',
            'training_count_last_3_years' => 'integer|min:0',
            
            // Association and Financial
            'is_association_member' => 'required|in:yes,no',
            'association_name' => 'nullable|string|max:255',
            'has_bank_account' => 'boolean',
            'bank_name' => 'nullable|string|max:255',
            'has_insurance' => 'boolean',
            'insurance_type' => 'nullable|string|max:255',
            
            // Market and Technology
            'main_market_outlet' => 'nullable|in:local_market,traders,cooperatives,direct_consumers,agri_companies',
            'distance_to_market_km' => 'nullable|numeric|min:0',
            'uses_improved_seeds' => 'boolean',
            'uses_organic_fertilizer' => 'boolean',
            'uses_chemical_fertilizer' => 'boolean',
            'uses_pesticides' => 'boolean',
            'has_farm_machinery' => 'boolean',
            'farm_machinery_owned' => 'nullable|string',
            
            // Crop Production Data
            'crop_productions' => 'nullable|array',
            'crop_productions.*.commodity_id' => 'required|exists:commodities,id',
            'crop_productions.*.area_planted_hectares' => 'required|numeric|min:0.01',
            'crop_productions.*.volume_produced_tons' => 'nullable|numeric|min:0',
            'crop_productions.*.farming_season' => 'required|in:wet_season,dry_season,year_round',
            'crop_productions.*.irrigation_type' => 'required|in:irrigated,rainfed,communal,pump',
            'crop_productions.*.seed_type' => 'required|in:hybrid,inbred,traditional,certified',
            
            // Livestock Production Data
            'livestock_productions' => 'nullable|array',
            'livestock_productions.*.livestock_type' => 'required|string',
            'livestock_productions.*.number_of_heads' => 'required|integer|min:1',
            'livestock_productions.*.purpose' => 'required|in:meat,dairy,eggs,breeding,draft_power,mixed',
            
            // Aquaculture Production Data
            'aquaculture_productions' => 'nullable|array',
            'aquaculture_productions.*.fish_species' => 'required|string',
            'aquaculture_productions.*.pond_area_hectares' => 'required|numeric|min:0.01',
            'aquaculture_productions.*.culture_type' => 'required|in:pond,cage,pen,rice_fish',
            
            // Document Requirements
            'documents' => 'required|array',
            'documents.has_id_photo' => 'required|boolean',
            'documents.has_government_id' => 'required|boolean',
            'documents.has_birth_certificate' => 'boolean',
            'documents.has_proof_of_address' => 'boolean',
            'documents.has_land_title' => 'boolean',
            'documents.has_tax_declaration' => 'boolean',
            
            // File uploads
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'government_id_photo' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'supporting_documents.*' => 'nullable|image|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        try {
            DB::beginTransaction();

            // Create or update user account
            $user = $this->createOrUpdateUser($validatedData);

            // Create or update beneficiary profile
            $beneficiaryProfile = $this->createOrUpdateBeneficiaryProfile($user, $validatedData, $request);

            // Create RSBSA enrollment
            $rsbsaEnrollment = $this->createRSBSAEnrollment($user, $beneficiaryProfile, $validatedData);

            // Store production data
            $this->storeCropProductions($rsbsaEnrollment, $validatedData['crop_productions'] ?? []);
            $this->storeLivestockProductions($rsbsaEnrollment, $validatedData['livestock_productions'] ?? []);
            $this->storeAquacultureProductions($rsbsaEnrollment, $validatedData['aquaculture_productions'] ?? []);

            // Store document requirements
            $this->storeDocumentRequirements($rsbsaEnrollment, $validatedData['documents']);

            // Upload supporting documents
            $this->uploadSupportingDocuments($rsbsaEnrollment, $request);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'RSBSA enrollment submitted successfully',
                'data' => [
                    'rsbsa_enrollment_id' => $rsbsaEnrollment->id,
                    'reference_code' => $rsbsaEnrollment->reference_code,
                    'status' => $rsbsaEnrollment->status
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('RSBSA enrollment failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'RSBSA enrollment failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get RSBSA enrollment details
     */
    public function show($id): JsonResponse
    {
        $enrollment = RSBSAEnrollment::with([
            'user',
            'beneficiaryProfile',
            'cropProductions.commodity',
            'livestockProductions',
            'aquacultureProductions',
            'documentRequirements'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $enrollment
        ]);
    }

    /**
     * Update RSBSA enrollment status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validatedData = $request->validate([
            'status' => 'required|in:pending,verifying,verified,rejected',
            'remarks' => 'nullable|string|max:1000',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $enrollment = RSBSAEnrollment::findOrFail($id);
        
        $enrollment->update([
            'status' => $validatedData['status'],
            'rejection_reason' => $validatedData['rejection_reason'] ?? null,
            'verified_by' => $validatedData['status'] === 'verified' ? Auth::id() : null,
            'verified_at' => $validatedData['status'] === 'verified' ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'RSBSA enrollment status updated successfully',
            'data' => $enrollment
        ]);
    }

    /**
     * Assign RSBSA number (by municipal staff)
     */
    public function assignRSBSANumber(Request $request, $id): JsonResponse
    {
        $validatedData = $request->validate([
            'rsbsa_number' => 'required|string|unique:beneficiary_profiles,RSBSA_NUMBER',
            'remarks' => 'nullable|string|max:500'
        ]);

        $enrollment = RSBSAEnrollment::findOrFail($id);
        
        if ($enrollment->status !== 'verified') {
            return response()->json([
                'success' => false,
                'message' => 'RSBSA enrollment must be verified first'
            ], 400);
        }

        // Update beneficiary profile with RSBSA number
        $enrollment->beneficiaryProfile->update([
            'RSBSA_NUMBER' => $validatedData['rsbsa_number'],
            'SYSTEM_GENERATED_RSBSA_NUMBER' => null, // Clear any system generated number
        ]);

        // Log the assignment in the enrollment record
        $enrollment->update([
            'rsbsa_assigned_by' => Auth::user()->fname . ' ' . Auth::user()->lname,
            'rsbsa_assignment_date' => now()->toDateString(),
            'rsbsa_assignment_remarks' => $validatedData['remarks'] ?? null,
            'rsbsa_number_assigned' => true,
            'rsbsa_number_assigned_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'RSBSA number assigned successfully',
            'data' => [
                'rsbsa_number' => $validatedData['rsbsa_number'],
                'assigned_by' => Auth::user()->fname . ' ' . Auth::user()->lname,
                'assignment_date' => now()->toDateString()
            ]
        ]);
    }

    /**
     * Validate RSBSA number (confirm it's working in DA system)
     */
    public function validateRSBSANumber(Request $request, $id): JsonResponse
    {
        $validatedData = $request->validate([
            'validation_status' => 'required|boolean',
            'validation_remarks' => 'nullable|string|max:500'
        ]);

        $enrollment = RSBSAEnrollment::findOrFail($id);
        $beneficiary = $enrollment->beneficiaryProfile;
        
        if (empty($beneficiary->RSBSA_NUMBER)) {
            return response()->json([
                'success' => false,
                'message' => 'RSBSA number must be assigned first'
            ], 400);
        }

        $beneficiary->update([
            'rsbsa_number_validated' => $validatedData['validation_status'],
            'rsbsa_number_validated_at' => $validatedData['validation_status'] ? now() : null,
            'rsbsa_validated_by' => $validatedData['validation_status'] ? Auth::id() : null,
        ]);

        // Add validation log to enrollment
        $enrollment->update([
            'rsbsa_assignment_remarks' => ($enrollment->rsbsa_assignment_remarks ?? '') . 
                "\nValidation: " . ($validatedData['validation_status'] ? 'VALID' : 'INVALID') . 
                " on " . now()->format('Y-m-d H:i') . 
                " by " . Auth::user()->fname . ' ' . Auth::user()->lname .
                ($validatedData['validation_remarks'] ? " - " . $validatedData['validation_remarks'] : "")
        ]);

        return response()->json([
            'success' => true,
            'message' => 'RSBSA number validation updated',
            'data' => [
                'rsbsa_number' => $beneficiary->RSBSA_NUMBER,
                'is_validated' => $beneficiary->rsbsa_number_validated,
                'validated_at' => $beneficiary->rsbsa_number_validated_at
            ]
        ]);
    }

    /**
     * Bulk assign RSBSA numbers from CSV/Excel
     */
    public function bulkAssignRSBSANumbers(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'assignments' => 'required|array|min:1',
            'assignments.*.enrollment_id' => 'required|exists:rsbsa_enrollments,id',
            'assignments.*.rsbsa_number' => 'required|string|unique:beneficiary_profiles,RSBSA_NUMBER',
            'batch_remarks' => 'nullable|string|max:1000'
        ]);

        $successCount = 0;
        $errors = [];

        DB::beginTransaction();
        
        try {
            foreach ($validatedData['assignments'] as $index => $assignment) {
                try {
                    $enrollment = RSBSAEnrollment::findOrFail($assignment['enrollment_id']);
                    
                    if ($enrollment->status !== 'verified') {
                        $errors[] = "Row " . ($index + 1) . ": Enrollment must be verified first";
                        continue;
                    }

                    // Update beneficiary profile
                    $enrollment->beneficiaryProfile->update([
                        'RSBSA_NUMBER' => $assignment['rsbsa_number'],
                        'SYSTEM_GENERATED_RSBSA_NUMBER' => null,
                    ]);

                    // Update enrollment record
                    $enrollment->update([
                        'rsbsa_assigned_by' => Auth::user()->fname . ' ' . Auth::user()->lname,
                        'rsbsa_assignment_date' => now()->toDateString(),
                        'rsbsa_assignment_remarks' => $validatedData['batch_remarks'] ?? 'Bulk assignment',
                        'rsbsa_number_assigned' => true,
                        'rsbsa_number_assigned_at' => now(),
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Bulk assignment completed: {$successCount} successful, " . count($errors) . " errors",
                'data' => [
                    'successful_assignments' => $successCount,
                    'total_assignments' => count($validatedData['assignments']),
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Bulk assignment failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get enrollments pending RSBSA number assignment
     */
    public function getPendingRSBSAAssignments(): JsonResponse
    {
        $pendingEnrollments = RSBSAEnrollment::with(['user', 'beneficiaryProfile'])
            ->where('status', 'verified')
            ->where('rsbsa_number_assigned', false)
            ->whereHas('beneficiaryProfile', function($query) {
                $query->whereNull('RSBSA_NUMBER');
            })
            ->orderBy('verified_at', 'asc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $pendingEnrollments,
            'summary' => [
                'total_pending' => $pendingEnrollments->total(),
                'oldest_pending' => $pendingEnrollments->first()?->verified_at,
            ]
        ]);
    }

    /**
     * Print RSBSA card
     */
    public function printCard($id): JsonResponse
    {
        $enrollment = RSBSAEnrollment::findOrFail($id);
        
        if (empty($enrollment->beneficiaryProfile->RSBSA_NUMBER)) {
            return response()->json([
                'success' => false,
                'message' => 'RSBSA number must be generated first'
            ], 400);
        }

        $enrollment->update([
            'card_printed' => true,
            'card_printed_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'RSBSA card marked as printed',
            'data' => $enrollment
        ]);
    }

    /**
     * Release RSBSA card
     */
    public function releaseCard(Request $request, $id): JsonResponse
    {
        $validatedData = $request->validate([
            'card_received_by' => 'required|string|max:255',
        ]);

        $enrollment = RSBSAEnrollment::findOrFail($id);
        
        $enrollment->update([
            'card_released' => true,
            'card_released_at' => now(),
            'card_received_by' => $validatedData['card_received_by']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'RSBSA card released successfully',
            'data' => $enrollment
        ]);
    }

    /**
     * Get RSBSA statistics
     */
    public function getStatistics(): JsonResponse
    {
        $currentYear = date('Y');
        $currentPeriod = $currentYear . '-' . ($currentYear + 1);

        $stats = [
            'total_enrollments' => RSBSAEnrollment::count(),
            'pending_applications' => RSBSAEnrollment::where('status', 'pending')->count(),
            'verified_applications' => RSBSAEnrollment::where('status', 'verified')->count(),
            'current_period_enrollments' => RSBSAEnrollment::where('enrollment_period', $currentPeriod)->count(),
            'cards_printed' => RSBSAEnrollment::where('card_printed', true)->count(),
            'cards_released' => RSBSAEnrollment::where('card_released', true)->count(),
            'by_livelihood' => BeneficiaryProfile::groupBy('main_livelihood')
                ->selectRaw('main_livelihood, count(*) as count')
                ->pluck('count', 'main_livelihood'),
            'by_barangay' => BeneficiaryProfile::groupBy('barangay')
                ->selectRaw('barangay, count(*) as count')
                ->pluck('count', 'barangay'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    // Private helper methods

    private function createOrUpdateUser(array $data): User
    {
        return User::updateOrCreate(
            ['email' => $data['email'] ?? null],
            [
                'fname' => $data['first_name'],
                'mname' => $data['middle_name'],
                'lname' => $data['last_name'],
                'extension_name' => $data['extension_name'],
                'username' => $data['username'] ?? $data['contact_number'],
                'phone_number' => $data['contact_number'],
                'role' => 'beneficiary',
                'password' => bcrypt($data['password'] ?? 'defaultpassword'),
            ]
        );
    }

    private function createOrUpdateBeneficiaryProfile(User $user, array $data, Request $request): BeneficiaryProfile
    {
        // Handle image uploads
        $profilePhoto = null;
        $govIdPhoto = null;
        $signaturePhoto = null;

        if ($request->hasFile('profile_photo')) {
            $profilePhoto = $this->imageUploadService->uploadImage(
                $request->file('profile_photo'),
                'profile'
            );
        }

        if ($request->hasFile('government_id_photo')) {
            $govIdPhoto = $this->imageUploadService->uploadImage(
                $request->file('government_id_photo'),
                'document'
            );
        }

        return BeneficiaryProfile::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($data, [
                'profile_photo' => $profilePhoto,
                'government_id_photo' => $govIdPhoto,
                'signature_photo' => $signaturePhoto,
            ])
        );
    }

    private function createRSBSAEnrollment(User $user, BeneficiaryProfile $beneficiaryProfile, array $data): RSBSAEnrollment
    {
        $currentYear = date('Y');
        $enrollmentPeriod = $currentYear . '-' . ($currentYear + 1);
        
        // Generate reference code
        $referenceCode = 'RSBSA-' . $currentYear . '-' . str_pad(RSBSAEnrollment::count() + 1, 6, '0', STR_PAD_LEFT);

        return RSBSAEnrollment::create([
            'user_id' => $user->id,
            'farm_profile_id' => $beneficiaryProfile->farmProfiles()->first()->id ?? null,
            'reference_code' => $referenceCode,
            'enrollment_period' => $enrollmentPeriod,
            'enrollment_type' => $data['enrollment_type'] ?? 'new',
            'application_method' => 'manual',
            'encoded_by' => Auth::id(),
            'encoding_date' => now(),
            'submitted_at' => now(),
            'registration_expires_at' => Carbon::now()->addYears(3), // RSBSA valid for 3 years
        ]);
    }

    private function storeCropProductions(RSBSAEnrollment $enrollment, array $cropProductions): void
    {
        foreach ($cropProductions as $crop) {
            RSBSACropProduction::create(array_merge($crop, [
                'rsbsa_enrollment_id' => $enrollment->id,
                'crop_year' => date('Y'),
            ]));
        }
    }

    private function storeLivestockProductions(RSBSAEnrollment $enrollment, array $livestockProductions): void
    {
        foreach ($livestockProductions as $livestock) {
            RSBSALivestockProduction::create(array_merge($livestock, [
                'rsbsa_enrollment_id' => $enrollment->id,
            ]));
        }
    }

    private function storeAquacultureProductions(RSBSAEnrollment $enrollment, array $aquacultureProductions): void
    {
        foreach ($aquacultureProductions as $aquaculture) {
            RSBSAAquacultureProduction::create(array_merge($aquaculture, [
                'rsbsa_enrollment_id' => $enrollment->id,
            ]));
        }
    }

    private function storeDocumentRequirements(RSBSAEnrollment $enrollment, array $documents): void
    {
        RSBSADocumentRequirement::create(array_merge($documents, [
            'rsbsa_enrollment_id' => $enrollment->id,
        ]));
    }

    private function uploadSupportingDocuments(RSBSAEnrollment $enrollment, Request $request): void
    {
        if ($request->hasFile('supporting_documents')) {
            $uploadedPaths = $this->imageUploadService->uploadMultipleImages(
                $request->file('supporting_documents'),
                'supporting'
            );
            
            // Update enrollment with uploaded document paths
            $enrollment->update([
                'supporting_documents' => json_encode($uploadedPaths)
            ]);
        }
    }


}