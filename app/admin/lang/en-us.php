<?php
return [
    'login' => [
        'not' => ucwords('please login'),
        'faultAccountPwd' => ucwords('the account or password is fault'),
        'noJwtSecret' => ucwords('if you are an administrator, enter jwt secret in env file'),
        'faultCaptcha' => ucwords('fault Captcha'),
        'updateLoginStatusFail' => ucwords('failed to update logging status.please try again soon'),
        'succ' => ucwords('congratulations!the page is redirecting to index'),
    ],
];