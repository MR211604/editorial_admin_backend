<?php

namespace App\Http\Traits;

trait NormalizesMdxImages
{
    protected function normalizeMdxImageUrls($content)
    {
        if (!is_string($content)) {
            return $content;
        }

        $pattern = '~(?:https?://[^\s"\'<>)]*|(?:/[^\s"\'<>)]*)?)/imagescontent/([A-Za-z0-9._-]+)(?:[?#][^\s"\'<>)]*)?|imagescontent/([A-Za-z0-9._-]+)(?:[?#][^\s"\'<>)]*)?~i';

        return preg_replace_callback($pattern, function ($matches) {
            return isset($matches[1]) && $matches[1] !== '' ? $matches[1] : $matches[2];
        }, $content);
    }

    protected function normalizeMdxImageFields(array $fields)
    {
        foreach ($fields as $key => $value) {
            $fields[$key] = $this->normalizeMdxImageUrls($value);
        }

        return $fields;
    }
}
