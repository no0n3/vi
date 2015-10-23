<?php
namespace models;

use helpers\ImageHelper;

abstract class BaseModel extends \base\Object {

    const TYPE_IMAGE = 'image';

    private $propertyErrors = [];

    public function getAttributeLabels() {
        return [];
    }

    public function load($data, $keyName = null) {
        $rules = $this->rules();

        if ('' === $keyName) {
            $modelData = $data;
        } else {
            $keyName = null !== $keyName ? $keyName : $this->getClassName(false);
            $modelData = $data[$keyName];
        }

        $modelName = $this->getClassName(false);

        foreach ($rules as $prop => $rules) {
            if (isset($modelData[$prop])) {
                $this->$prop = $modelData[$prop];
            } else if(true ||
                isset($_FILES[$modelName]) &&
                isset($_FILES[$modelName]['tmp_name'][$prop])
            ) {
                if (!isset($rules['type']) ||
                    self::TYPE_IMAGE !== $rules['type'] ||
                    !preg_match("/image\/.*/", $_FILES[$modelName]['type'][$prop])
                ) {
                    return false;
                }
                $this->$prop = ImageHelper::loadImage($_FILES[$modelName]['tmp_name'][$prop]);

                if (!$this->$prop) {
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

    public function rules() {
        return [];
    }

    public function validate() {
        foreach ($this->rules() as $prop => $rules) {
            if (!$this->validateProperty($prop, $rules)) {
                return false;
            }
        }

        return true;
    }

    public function validateProperty($prop, $rules) {
        if (empty($rules)) {
            return true;
        } else if (!$this->hasProperty($prop)) {
            return false;
        }

        if (isset($rules['filter'])) {
            $this->$prop = $rules['filter']($this->$prop);
        }

        $propType = isset($rules['type']) ? $rules['type'] : null;

        if (null !== $propType) {
            if ('int' === $propType) {
                if (is_numeric($this->$prop)) {
                    $this->addError($prop, "$prop must be an integer.");
                    return false;
                }
                if (isset($rules['min']) && $rules['min'] > $this->$prop) {
                    $this->addError($prop, sprintf("%s cannot be less than %d.", $prop, $rules['min']));
                    return false;
                }
                if (isset($rules['max']) && $rules['max'] < $this->$prop) {
                    $this->addError($prop, sprintf("%s cannot be greather than %d.", $prop, $rules['max']));
                    return false;
                }
            } else if ('string' === $propType || 'text' === $propType) {
                if (!is_string($this->$prop)) {
                    $this->addError($prop, "$prop must be a string.");
                    return false;
                }
                if (isset($rules['min']) && $rules['min'] > strlen($this->$prop)) {
                    $this->addError($prop, "$prop must be at least {$rules['min']} characters long.");
                    return false;
                }
                if (isset($rules['max']) && $rules['max'] < strlen($this->$prop)) {
                    $this->addError($prop, "$prop maximum length must {$rules['max']} characters.");
                    return false;
                }
            } else if ('email' === $propType) {
                if (!filter_var($this->$prop, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($prop, "Please enter a valid email.");
                    return false;
                }
            }
        }

        return isset($rules['validator']) ? $rules['validator']($prop, $this->$prop) : true;
    }

    public function addError($prop, $message) {
        $this->propertyErrors[$prop] = $message;
    }

    public function getError($prop) {
        return isset($this->propertyErrors[$prop]) ? $this->propertyErrors[$prop] : null;
    }

    public static function getPostedAgoTime($postedAgo) {
        if (empty($postedAgo)) {
            return 'N/A';
        }

        $currenttime = time();
        $time = strtotime($postedAgo);

        $currenttime = (int) strtotime(date('Y-m-d H:i:s', time())) * 1000;
        $time = (int) strtotime($postedAgo) * 1000;

        $dif = ( $currenttime - $time ) / 1000;
        $dif = (int) $dif;

        if ($dif >= 60) {
            $dif = (int) ($dif / 60);
            if ($dif >= 60) {
                $dif = (int) ($dif / 60);
                if ($dif >= 24) {
                    $dif = (int) ($dif / 24);
                    if ($dif >= 7) {
                        $dif = (int) ($dif / 7);
                        if ($dif >= 4) {
                            $dif = (int) ($dif / 4);
                            if ($dif >= 12) {
                                $dif = (int) ($dif / 12);
                                return "$dif years ago";
                            } else {
                                // moths
                                return "$dif months ago";
                            }
                        } else {
                            // weeks
                            return "$dif weeks ago";
                        }
                    } else {
                        // days
                        return "$dif days ago";
                    }
                } else {
                    // hours
                    return "$dif hours ago";
                }
            } else {
                // mins
                return "$dif minutes ago";
            }
        } else {
            return 'just_now';
        }
    }
}
