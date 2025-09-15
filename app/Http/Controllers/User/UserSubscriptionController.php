<?php

namespace App\Http\Controllers\User;

use App\Models\Collaborator;
use App\Models\Collection;
use App\Models\User;
use App\Models\UserSubscription;
use App\Http\Controllers\ApiController;
use App\Models\WebsiteCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserSubscriptionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(User $user, Request $request)
    {
        $subscriptions = $user->userSubscription();
    
        if ($request->has('collaborator_id')) {
            $collabId = $request->collaborator_id;
            $subscriptions->where('collaborator_id', $collabId);
        }
    
        if ($request->has('collection_id')) {
            $collecId = $request->collection_id;
            $subscriptions->where('collection_id', $collecId);
        }

        
        if ($request->has('category_id')) {
            $catId = $request->category_id;
            $subscriptions->where('category_id', $catId);
        }
    
        // Add condition for ecom_type
        if ($request->has('ecom_type')) {
            $subscriptions->where('ecom_type', $request->ecom_type);
        }
    
        $subscriptions = $subscriptions->get();
    
        if ($subscriptions->isEmpty()) {
            return $this->errorResponse('You have no available subscriptions.', 404);
        }
    
        return $this->showAll($subscriptions);
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, User $user)
    {
        $data = $request->all();
        $data['user_id'] = $user->id;
    
        if ($request->has('collaborator_id')) {
            $collab = Collaborator::find($request->collaborator_id);
            if (!$collab) {
                return $this->errorResponse('This collaborator does not exist.', 404);
            }

            // Check if the collection ID is already subscribed
            $isSubscribed = UserSubscription::where('collaborator_id', $request->collaborator_id)->exists();
            if ($isSubscribed) {
                return $this->errorResponse('This collaborator is already subscribed.', 422);
            }
        }
    
        if ($request->has('collection_id')) {
            $collec = Collection::find($request->collection_id);
            if (!$collec) {
                return $this->errorResponse('This collection does not exist.', 404);
            }
    
            // Check if the collection ID is already subscribed
            $isSubscribed = UserSubscription::where('collection_id', $request->collection_id)->exists();
            if ($isSubscribed) {
                return $this->errorResponse('This collection is already subscribed.', 422);
            }
        }

        
        if ($request->has('category_id')) {
            $collec = WebsiteCategory::find($request->category_id);
            if (!$collec) {
                return $this->errorResponse('This category does not exist.', 404);
            }
    
            // Check if the collection ID is already subscribed
            $isSubscribed = UserSubscription::where('category_id', $request->category_id)->exists();
            if ($isSubscribed) {
                return $this->errorResponse('This category is already subscribed.', 422);
            }
        }
    
        $usersubs = UserSubscription::create($data);
    
        return $this->showOne($usersubs, 201);
    }
    

    /**
     * Display the specified resource.
     */
    public function show(User $user, UserSubscription $userSubscription)
    {
        $userSubscription = UserSubscription::where('user_id', $user->id)
            ->where('id', $userSubscription->id)
            ->first();

        if (!$userSubscription) {
            return response()->json(['error' => 'Address not found'], 404);
        }

        return $this->showOne($userSubscription);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserSubscription $userSubscription)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, UserSubscription $Subscription)
    {
        // Delete the user subscription
        $this->checkAddress($user, $Subscription);
        $Subscription->delete();
    
        // Return success response
        return $this->showOne($Subscription);
    }

  
    protected function checkAddress(User $user, UserSubscription $Subscription){
        if($user->id != $Subscription->user_id){
            throw new HttpException(422,'The Specified user is not the actual user of the address');
        }
    }
}
