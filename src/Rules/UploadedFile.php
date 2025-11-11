<?php

declare(strict_types=1);

namespace Qubus\Validation\Rules;

use Qubus\Validation\Helper;
use Qubus\Validation\MimeTypeGuesser;
use Qubus\Validation\Rule;
use Qubus\Validation\Rules\Interfaces\BeforeValidate;
use Qubus\Validation\Rules\Traits\FileAware;
use Qubus\Validation\Rules\Traits\SizeAware;

class UploadedFile extends Rule implements BeforeValidate
{
    use FileAware;
    use SizeAware;

    protected string $message = "The :attribute is not valid uploaded file";

    protected string|int|null $maxSize = null;

    protected string|int|null $minSize = null;

    protected array $allowedTypes = [];

    /**
     * Given $params and assign $this->params.
     *
     * @param array $params
     * @return self
     */
    public function fillParameters(array $params): Rule
    {
        $this->minSize(array_shift($params));
        $this->maxSize(array_shift($params));
        $this->fileTypes($params);

        return $this;
    }

    /**
     * Given $size and set the max size
     *
     * @param int|string|null $size
     * @return self
     */
    public function maxSize(int|string|null $size = null): Rule
    {
        $this->params['max_size'] = $size;
        return $this;
    }

    /**
     * Given $size and set the min size
     *
     * @param int|string|null $size
     * @return self
     */
    public function minSize(int|string|null $size = null): Rule
    {
        $this->params['min_size'] = $size;
        return $this;
    }

    /**
     * Given $min and $max then set the range size
     *
     * @param int|string|null $min
     * @param int|string|null $max
     * @return self
     */
    public function sizeBetween(int|string|null $min = null, int|string|null $max = null): Rule
    {
        $this->minSize($min);
        $this->maxSize($max);

        return $this;
    }

    /**
     * Given $types and assign $this->params
     *
     * @param mixed $types
     * @return self
     */
    public function fileTypes(mixed $types): Rule
    {
        if (is_string($types)) {
            $types = explode('|', $types);
        }

        $this->params['allowed_types'] = $types;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeValidate(): void
    {
        $attribute = $this->getAttribute();

        // We only resolve uploaded file value
        // from complex attribute such as 'files.photo', 'images.*', 'images.foo.bar', etc.
        if (!$attribute->isUsingDotNotation()) {
            return;
        }

        $keys = explode(".", $attribute->getKey());
        $firstKey = array_shift($keys);
        $firstKeyValue = $this->validation->getValue($firstKey);

        $resolvedValue = $this->resolveUploadedFileValue($firstKeyValue);

        // Return original value if $value can't be resolved as uploaded file value
        if (!$resolvedValue) {
            return;
        }

        $this->validation->setValue($firstKey, $resolvedValue);
    }

    /**
     * Check the $value is valid.
     *
     * @param mixed $value
     * @return bool
     */
    public function check(mixed $value): bool
    {
        $minSize = $this->parameter('min_size');
        $maxSize = $this->parameter('max_size');
        $allowedTypes = $this->parameter('allowed_types');

        if ($allowedTypes) {
            $or = $this->validation ? $this->validation->getTranslation('or') : 'or';
            $this->setParameterText('allowed_types', Helper::join(Helper::wraps($allowedTypes, "'"), ', ', ", {$or} "));
        }

        // below is Required rule job
        if (!$this->isValueFromUploadedFiles($value) or $value['error'] == UPLOAD_ERR_NO_FILE) {
            return true;
        }

        if (!$this->isUploadedFile($value)) {
            return false;
        }

        // just make sure there is no error
        if ($value['error']) {
            return false;
        }

        if ($minSize) {
            $bytesMinSize = $this->getBytesSize($minSize);
            if ($value['size'] < $bytesMinSize) {
                $this->setMessage('The :attribute file is too small, minimum size is :min_size');
                return false;
            }
        }

        if ($maxSize) {
            $bytesMaxSize = $this->getBytesSize($maxSize);
            if ($value['size'] > $bytesMaxSize) {
                $this->setMessage('The :attribute file is too large, maximum size is :max_size');
                return false;
            }
        }

        if (!empty($allowedTypes)) {
            $guesser = new MimeTypeGuesser();
            $ext = $guesser->getExtension($value['type']);
            unset($guesser);

            if (!in_array($ext, $allowedTypes)) {
                $this->setMessage('The :attribute file type must be :allowed_types');
                return false;
            }
        }

        return true;
    }
}
