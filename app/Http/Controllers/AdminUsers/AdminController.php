<?php

namespace App\Http\Controllers\AdminUsers;

use App\Http\Controllers\ApiController;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;


class AdminController extends ApiController
{

    //Register Api
    public function register(Request $request)
    {

        //Data Validation
        $rules = [
            'email' => 'required|email',
        ];

        $this->validate($request, $rules);

        $data = $request->all();  
            $data['password'] = bcrypt($request->password);

        $existingUser = AdminUser::where('email', $request->email)
            ->first();

        if ($existingUser) {
            return $this->errorResponse('The email already exists for a user.', 400);
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
        $data['active'] = AdminUser::ACTIVE_USER;

        $user = AdminUser::create($data);

        return $this->showOne($user, 201);
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
            'password' => 'required',
        ]);
    
        // Retrieve user by email and type
        $user = AdminUser::where('email', $request->email)->first();
    
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
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
                return $this->errorResponse('Incorrect password. Forgot your password?', 401);
            }
        } else {
            return $this->errorResponse('Incorrect e-mail address.', 404);
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
        $user = AdminUser::find($userId);

        $user->update($request->all());

        return $user;
    }

    public function logout()
    {

        auth()->user()->token()->revoke();


        return response()->json([
            'status' => true,
            'message' => 'User Logged out successfully',
        ]);

    }
}
