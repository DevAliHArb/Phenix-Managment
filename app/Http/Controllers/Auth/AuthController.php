<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\ApiController;
use App\Models\GuestUsers;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;


class AuthController extends ApiController
{

    //Register Api
    public function register(Request $request)
    {

        //Data Validation
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
        ];

        $this->validate($request, $rules);

        $data = $request->all();  
            $data['password'] = bcrypt($request->password);

        $existingUser = User::where('email', $request->email)
            ->where('type', $request->type)
            ->first();

        if ($existingUser) {
            return $this->errorResponse('The email already exists for a user with the specified type.', 400);
        }


        if ($request->has('image')) {
            $imageData = $request->image;

            // Check if the image data is base64 encoded
            if (Str::startsWith($imageData, 'data:image')) {
                // Extract the image extension from the base64 string
                $extension = explode('/', mime_content_type($imageData))[1];
                // Generate a unique filename
                $filename = strtolower(time() . '_' . Str::random(10) . $extension);
                // Decode base64 image data
                $decodedImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));
                // Save the decoded image to the public/img folder
                file_put_contents(public_path('img/' . $filename), $decodedImage);
                // Convert the image to WebP format
                // $this->convertToWebP(public_path('img/' . $filename));
                // Update the image field with the filename
                $data['image'] = $filename;
            } elseif ($request->hasFile('image')) {
                // Get uploaded image
                $image = $request->file('image');
                // Generate a unique filename
                $filename = strtolower(time() . '_' . Str::random(10) . '.webp');
                // Move the uploaded image to the public/img folder
                $image->move(public_path('img'), $filename);
                // Update the image field with the filename
                $data['image'] = $filename;
            }
        } else {
            $data['image'] = 'user.png';
        }


        //  $data['image'] = $request->hasFile('image') ? $request->image->store('', 'images') : null;
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationCode();
        $data['active'] = User::ACTIVE_USER;
        $data['blocked'] = User::UNBLOCKED_USER;

        $user = User::create($data);

        event(new Registered($user));

        return $this->showOne($user, 201);

        //create User
    }

    public function guestRegister(Request $request)
    {
        // Data Validation
        $rules = [
            'email' => 'required|email',
        ];

        $this->validate($request, $rules);

        $data = $request->all();

        $existingUser = GuestUsers::where('email', $request->email)->first();

        if ($existingUser) {
            // Check if verified (using isVerified method from GuestUsers model)
            if ($existingUser->isVerified()) {
                return response()->json(['verified' => true, 'user' => $existingUser], 200);
            } else {
                // Not verified, send OTP again
                $existingUser->generateOtp();
                return $this->showOne($existingUser, 200);
            }
        }

        // Create new guest user
        $user = GuestUsers::create($data);
        $user->generateOtp();

        return $this->showOne($user, 201);
    }

    
public function VerifyGuestUser(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'otp' => 'required'
    ]);

    $user = GuestUsers::where('email', $request->email)
        ->first();

    if (!$user) {
        return $this->errorResponse('User not found.', 404);
    }

    $otp = $request->otp;

    if (
        $user->otp_code === $otp &&
        $user->otp_expires_at &&
        now()->lt($user->otp_expires_at) &&
        $user->id
    ) {
        // Optionally mark as verified
        $user->email_verified_at = now();
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json(['message' => 'OTP verified successfully', 'user' => $user]);
    } else {
        return $this->errorResponse('Invalid or expired OTP.', 400);
    }
}
    private function convertToWebP($filePath)
    {
        $image = imagecreatefromstring(file_get_contents($filePath));
        imagewebp($image, $filePath, 80);
        imagedestroy($image);
    }
    //Login Api
    
    public function login(Request $request)
{
    // Data Validation
    $request->validate([
        'email' => 'required|email',
        'password' => 'required_if:google,false', // Password is required if google is false
        'type' => 'required' 
    ]);

    // Retrieve user by email and type
    $user = User::where('email', $request->email)
                ->where('type', $request->type)
                ->first();

    if ($user) {
        // Check if Google login is enabled and password is not required
        if ($user->google === 'true') {
            // Check if the user is verified
            if (!$user->hasVerifiedEmail()) {
                return $this->errorResponse('Please verify your email.', 403);
            }

            // Login the user
            Auth::login($user);

            // Generate access token
            $token = $user->createToken('myToken')->accessToken;

            // Return response
            return response()->json([
                'status' => true,
                'message' => 'Logged in successfully',
                'user' => $user,
                'token' => $token,
            ]);
        }

        // If not using Google login or Google login is disabled, proceed with regular login
        if (Hash::check($request->password, $user->password)) {
            // Check if the user is verified
            if (!$user->hasVerifiedEmail()) {
                return $this->errorResponse('Please verify your email.', 403);
            }
            
            // Login the user
            Auth::login($user);

            // Generate access token
            $token = $user->createToken('myToken')->accessToken;

            // Return response
            return response()->json([
                'status' => true,
                'message' => 'Logged in successfully',
                'user' => $user,
                'token' => $token,
            ]);
        } else {
            // Incorrect password
            return $this->errorResponse('Adresse e-mail ou mot de passe incorrect. Mot de Passe Oublié?', 401);
        }
    } else {
        // User not found
        return $this->errorResponse('Adresse e-mail ou mot de passe incorrect. Mot de Passe Oublié?', 404);
    }
}


    //Profile Api (GET)
    public function profile()
    {

        $user = Auth::user();

        return response()->json([
            'status' => true,
            'message' => 'Profile Information',
            'data' => $user
        ]);
    }

    public function editProfile(Request $request)
    {
        $userId = Auth::id();
        $user = User::find($userId);

        $user->update($request->all());

        return $user;
    }

    //Logout Api (GET)
    public function logout()
    {

        auth()->user()->token()->revoke();


        return response()->json([
            'status' => true,
            'message' => 'User Logged out successfully',
        ]);

    }
}
