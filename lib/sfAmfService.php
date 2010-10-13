<?php
/**
 * This file is part of the sfAmfPlugin package.
 * (c) 2008-2009 Timo Haberkern <timo.haberkern@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class sfAmfService {
    public function __construct() {
        $class_name = get_class($this);
        $reflector = new ReflectionClass($class_name);

        foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (!$method->isConstructor() &&
                !$method->isStatic() &&
                !$method->isAbstract() ) {

            // getting the annotation information for this method
                $method_reflector = new ReflectionAnnotatedMethod($class_name, $method->name);

                if ($method_reflector->hasAnnotation('AmfClassMapping')) {
                    $as_class_name = $method_reflector->getAnnotation('AmfClassMapping')->name;
                    $this->method_info[$method->name]['class_mapping'] = $as_class_name;
                }

                if ($method_reflector->hasAnnotation('AmfIgnore')) {
                    $this->method_info[$method->name]['ignore'] = 1;
                }

                if ($method_reflector->hasAnnotation('AmfReturnType')) {
                    $type = $method_reflector->getAnnotation('AmfReturnType')->value;

                    // At the moment we can use ByteArray and ArrayCollection as
                    // types
                    if ($type == 'ArrayCollection' || $type == 'ByteArray') {
                        $this->method_info[$method->name]['return_type'] = $type;
                    }
                    else {
                        throw new Exception('AmfReturnType can only be one of the following values: '.
                            'ByteArray, ArrayCollection');
                    }
                }
            }
        }
    }

    public function run($method_name, $arguments) {

        if (array_key_exists($method_name, $this->method_info)) {
            if (array_key_exists('ignore', $this->method_info[$method_name])) {
                if ($this->method_info[$method_name]['ignore'] == 1) {
                    throw new Exception('There is no callable method with the name '.
                        $method_name.
                        '. Access restricted by @AmfIgnore.');
                }
            }
        }
        
        $result = call_user_func_array(
            array($this, $method_name),
            $arguments
        );
        $adapter_proxy = new sfAdapterDelegate();
        $data = $adapter_proxy->convert($result);

        if (array_key_exists($method_name, $this->method_info)) {
            if (array_key_exists('class_mapping', $this->method_info[$method_name])) {
                if ($this->method_info[$method_name]['class_mapping'] == '') {
                    throw new Exception('Class mapping can not be done. Empty class name. '.
                        'Please use a format like @AmfClassMapping(name="de.rtx.mipmap")');
                }
                else {
                    return new SabreAMF_TypedObject(
                    $this->method_info[$method_name]['class_mapping'],
                    $data);
                }
            }
            else if (array_key_exists('return_type', $this->method_info[$method_name])) {
                    switch ($this->method_info[$method_name]['return_type']) {
                        case 'ArrayCollection':
                            return new SabreAMF_ArrayCollection($data);
                            break;
                        case 'ByteArray':
                            return new SabreAMF_ByteArray($data);
                            break;
                    }
                }
        }

        return $data;
    }

    private $method_info = array();
}
?>