<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
class Admin extends Authenticatable
{
  use HasApiTokens, HasFactory, Notifiable, HasRoles;

  protected $guard = 'admin'; // Specify the guard name
  protected $guard_name = 'admin';
  protected $fillable = [
    'name',
    'email',
    'password',
    'api_token',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];
  protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed',
  ];
}