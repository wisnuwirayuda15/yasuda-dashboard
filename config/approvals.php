<?php

// config for EightyNine/Approval
return [
  "role_model" => \Spatie\Permission\Models\Role::class,
  "navigation" => [
    "should_register_navigation" => true,
    "icon" => "fluentui-checkbox-person-16",
    "sort" => 1
  ],
  "enable_approval_comments" => false, // Allows also commenting on approvals
  "enable_rejection_comments" => true, // Allows also commenting on rejections
];
