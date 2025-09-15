<?php

    namespace App\Http\Controllers\User;

    use App\Http\Controllers\ApiController;
    use App\Models\User;
    use App\Notifications\AccountDeletionNotification;
    use Illuminate\Http\Request;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Str;

    class UserController extends ApiController
    {
        
        /**
         * Display a listing of the resource.
         */
        public function index(Request $request)
        {
            if ($request->has('email')) {
                $user = User::where('email', $request->email)->with(['userAddresses','orderInvoices'=> function ($query) {
                    $query->with('lookUp','orderInvoiceItems');
            },])->first();
                if (!$user) {
                    return response()->json([], 200);
                }
                return $this->showOne($user);
            }
            $query = User::query();
            $users = $query->with(['userAddresses','orderInvoices'=> function ($query) {
                $query->with('lookUp','orderInvoiceItems');
        }, ])->get();

            return $this->showAll($users);
            // return $users
        }

        /**
         * Store a newly created resource in storage.
         */
        // public function store(Request $request)
        // {
        //     $rules = [
        //         'first_name' => 'required',
        //         'last_name' => 'required',
        //         'email' => 'required|email|unique:users',
        //         'password' => 'required|min:8',
        //     ];

        //     $this->validate($request, $rules);

        //     $data = $request->all();
        //     $data['password'] = bcrypt($request->password);
        //  $data['image'] = $request->hasFile('image') ? $request->image->store('', 'images') : null;
        //     $data['verified'] = User::UNVERIFIED_USER;
        //     $data['verification_token'] = User::generateVerificationCode();
        //     $data['active'] = User::ACTIVE_USER;
        //     $data['blocked'] = User::UNBLOCKED_USER;

        //     $user = User::create($data);

        //     return $this->showOne($user, 201);
        // }

        /**
         * Display the specified resource.
         */
        public function show(User $user)
        {
            return $this->showOne($user);
        }

        /**
         * Update the specified resource in storage.
         */
        public function update(Request $request, User $user)
        {

            $rules = [
                'email' => 'email|unique:users,email,' . $user->id,
                'password' => 'min:8',
                'active' => 'in:' . User::ACTIVE_USER . ',' . User::UNACTIVE_USER,
                'google' => 'in:' . User::GOOGLE_USER . ',' . User::REGULAR_USER,
            ];

            $this->validate($request, $rules);

            if ($request->has('first_name')) {
                $user->first_name = $request->first_name;
            }

            if ($request->has('last_name')) {
                $user->last_name = $request->last_name;
            }

            if ($request->has('phone')) {
                $user->phone = $request->phone;
            }
            
            if ($request->has('currency')) {
                $user->currency = $request->currency;
            }

            if ($request->has('language')) {
                $user->language = $request->language;
            }
            if ($request->has('company_name')) {
                $user->company_name = $request->company_name;
            }
            if ($request->has('company_city')) {
                $user->company_city = $request->company_city;
            }
            if ($request->has('company_address')) {
                $user->company_address = $request->company_address;
            }
            if ($request->has('tva')) {
                $user->tva = $request->tva;
            }
            if ($request->has('siret')) {
                $user->siret = $request->siret;
            }

            if ($request->has('image')) {
                // Handle base64 or uploaded image
                if (Str::startsWith($request->image, 'data:image')) {
                // Base64 image handling
                $imageData = substr($request->image, strpos($request->image, ',') + 1);
                $imageData = base64_decode($imageData);
                $filename = strtolower(time() . '_' . Str::random(10) . '.webp');
            
                // Save the decoded image to the public/img folder
                Storage::disk('public')->put( $filename, $imageData);
            
                // Delete previous image if exists (already correct)
                if ($user->image) {
                    Storage::disk('public')->delete('img/' . $user->image);
                }
            
                $user->image = $filename;
                } else {
                // Uploaded file handling
                $file = $request->file('image');
                if ($file->isValid() && in_array($file->getClientMimeType(), ['image/jpeg', 'image/png'])) {
                    $filename = strtolower(time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension()); // Use original extension
            
                    // Store the uploaded image in the public/img folder
                    $file->storePubliclyAs( $filename);
            
                    // Delete previous image if exists (already correct)
                    if ($user->image) {
                    Storage::disk('public')->delete('img/' . $user->image);
                    }
            
                    $user->image = $filename;
                } else {
                    return response()->json(['error' => 'Invalid image format'], 400);
                }
                }
            }
            



            if ($request->has('email') && $user->email != $request->email) {
                $user->verified = User::UNVERIFIED_USER;
                $user->verification_token = User::generateVerificationCode();
                $user->email = $request->email;
            }

            if ($request->has('newpassword')) {
                if (!Hash::check($request->currentPassword, $user->password)) {
                    return response()->json(['error' => 'Current password is incorrect'], 401);
                }

                if ($request->currentPassword === $request->newpassword) {
                    return response()->json(['error' => 'Please enter a new password different from the current one'], 400);
                }
            
                $user->password = bcrypt($request->newpassword);
                $user->save();
            
                return response()->json(['message' => 'Password changed successfully']);
            }
            

            if ($request->has('active')) {
                if (!$user->isVerified()) {
                    return $this->errorResponse('Only verified users can modify the active field', 409);
                }
                $user->active = $request->active;
            }

            if (!$user->isDirty()) {
                return $this->errorResponse('You nedd to specify a different value to update', 422);
            }
            $user->save();
            return $this->showOne($user);
        }

        
        public function destroy(User $user)
        {
            $user->delete_token = Str::random(60); // Generate a random token
            $user->save();
        
            // Send deletion confirmation email
            $user->notify(new AccountDeletionNotification($user));
        
            return response()->json([
                'message' => 'A confirmation email has been sent to your registered email address. Please check your inbox to confirm account deletion.'
            ], 200);
        }

    public function confirmAccountDeletion(Request $request, User $user)
    {

        $redirectUrl = $this->getRedirectUrlByUserType($user);

        if (!$request->hasValidSignature()) {
            return redirect()->away($redirectUrl)->with('error', 'Invalid or expired deletion link.');
        }

        // Perform deletion logic
        if ($user->image) {
            Storage::disk('public')->delete($user->image);
        }
        $user->delete();

        return redirect()->away($redirectUrl)->with('success', 'Your account has been successfully deleted.');
    }

    protected function getRedirectUrlByUserType($user)
    {
        switch ($user->type) {
            case 'bookshop':
                return 'https://bookshopwebsite.pages.dev/';
            case 'sofiaco':
                return 'https://sofiaco-website.pages.dev/';
            case 'albouraq':
                return 'https://albouraq-website.pages.dev/';
            case 'maktabox':
                return 'https://maktabox-website.pages.dev/';
            default:
                return 'https://default-website.pages.dev/';
        }
    }
    }
