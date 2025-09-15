<?php

    namespace App\Http\Controllers\AdminUsers;

    use App\Http\Controllers\ApiController;
    use App\Models\AdminUser;
    use App\Models\User;
    use App\Notifications\AccountDeletionNotification;
    use Illuminate\Http\Request;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Str;

    class AdminUserController extends ApiController
    {
        
        /**
         * Display a listing of the resource.
         */
        public function index(Request $request)
        {
            if ($request->has('email')) {
                $user = AdminUser::where('email', $request->email)->first();
                if (!$user) {
                    return response()->json([], 200);
                }
                return $this->showOne($user);
            }
            $users = AdminUser::all();

            return $this->showAll($users);
            // return $users
        }

        public function show(AdminUser $user)
        {
            return $this->showOne($user);
        }

        /**
         * Update the specified resource in storage.
         */
        public function update(Request $request, $id)
{
    $user = AdminUser::findOrFail($id);

    // Data Validation
    // $rules = [
    //     'email' => 'required|email|unique:admin_users,email,' . $user->id,
    //     'password' => 'sometimes|min:6',
    // ];

    // $this->validate($request, $rules);

    $data = $request->all();

    if ($request->has('password')) {
        $data['password'] = bcrypt($request->password);
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
    }

    $user->update($data);

    return $this->showOne($user, 200);
}


        
public function destroy($id)
{
    $user = AdminUser::findOrFail($id);

    // Delete the user's image if it exists and is not the default image
    if ($user->image && $user->image !== 'user.png') {
        $imagePath = public_path('img/' . $user->image);

        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete the user from the database
    $user->delete();

    return $this->showOne($user, 200);
}

    }