<?php
namespace App\Http;

class CustomValidator {
    public function validateStrength($attribute, $value, $parameters, $validator) {
      if( preg_match('/(?=^.{8,}$)(?=.*\d)(?=.*[!@#$%^&*]+)(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/', $value) )
          return true;
      return false;
    }
}