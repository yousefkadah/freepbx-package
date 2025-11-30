<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('freepbx.extension.{extension}', function ($user, $extension) {
    // Check if user has access to this extension
    // Customize this based on your application's logic
    return $user->extension === $extension;
});

Broadcast::channel('freepbx.tenant.{tenantId}', function ($user, $tenantId) {
    // Check if user belongs to this tenant
    // Customize this based on your application's logic
    return $user->tenant_id === $tenantId;
});

Broadcast::channel('freepbx.dashboard', function ($user) {
    // Check if user has permission to view dashboard
    // Customize this based on your application's logic
    return true; // Or implement your authorization logic
});
