<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Services\SmsService;
use App\Http\Repositories\AdminRepository;

use Kreait\Firebase\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class AdminController extends BaseController
{
    private $smsService;
    private $repo; 
    public function __construct(SmsService $smsService, AdminRepository $repo, Messaging $messaging)
    {
        $this->smsService = $smsService;
        $this->repo = $repo; 
        $this->messaging = $messaging;
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function show(Admin $admin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Admin $admin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function destroy(Admin $admin)
    {
        //
    }
    
    public function testNotif()
    { 
        
        $message = CloudMessage::withTarget('topic', "news")
        ->withNotification(Notification::create("title", "body")) 
        ->withData(['type' => 'type_1']);
        $this->messaging->send($message); 

        return $this->sendResponse($this->messaging->send($message));
    }
}
