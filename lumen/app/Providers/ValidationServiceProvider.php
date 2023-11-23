<?php
declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Validator;

class ValidationServiceProvider extends ServiceProvider
{
    const MAX_COUNTRY_CODE_LENGTH = 2;

    public function register()
    {
        $this->app['validator']->extend(
            'one_of',
            function ($attribute, $value, $parameters) {
                if (empty($value)) {
                    return false;
                }

                foreach ($parameters as $parameter) {
                    if (isset($value[$parameter]) && !empty($value[$parameter])) {
                        return true;
                    }
                }

                return false;
            }
        );

        $this->app['validator']->extend(
            'country_code',
            function ($attribute, $value) {
                return (is_string($value) && strlen($value) === self::MAX_COUNTRY_CODE_LENGTH && ctype_alpha($value));
            }
        );

        $this->app['validator']->extend(
            'boolean_only',
            function ($attribute, $value) {
                return is_bool($value);
            }
        );

        $this->app['validator']->extend(
            'month',
            function ($attribute, $value) {
                return in_array($value, range(1,12));
            }
        );

        $this->app['validator']->extend(
            'year',
            function ($attribute, $value) {
                return ((int) $value) > 1900;
            }
        );

        $this->app['validator']->extend(
            'allowed_with',
            function ($attribute, $value, $parameters, $validator) {
                /** @var Validator $validator */

                if(!Arr::has($validator->getData(), $attribute)) {
                    return true;
                }

                foreach ($parameters as $key => $parameter) {
                    if (empty(Arr::get($validator->getData(), str_replace('*', $key, $parameter)))) {
                        return false;
                    }
                }

                return true;
            }
        );

        $this->app['validator']->extend(
            'same_string',
            function ($message, $attribute, $rule, $parameters) {
                $input = Arr::get($parameters->getData(), $rule[0]);
                if (is_array($input)) {
                    return false;
                }

                return ((string) $input === (string) $attribute);
            }
        );

        $this->app['validator']->extend(
            'is_positive_int',
            function ($attribute, $value) {
                return (is_int($value) && $value >= 0);
            }
        );

        $this->app['validator']->replacer(
            'required_with',
            function ($message, $attribute, $rule, $parameters) {
                return str_replace(':others', '\'' . implode('\', \'', $parameters) . '\'', $message);
            }
        );

        // The Laravel's "integer" validation rule does not verify that the input is of the "integer" variable type,
        // only that the input is a string or numeric value that contains an integer.
        // see https://laravel.com/docs/5.8/validation#rule-integer
        $this->app['validator']->extend(
            'is_int',
            function ($attribute, $value) {
                return is_int($value);
            }
        );

        $this->app['validator']->extend(
            'is_float',
            function ($attribute, $value) {
                return is_float($value) || is_int($value);
            }
        );

        $this->app['validator']->replacer(
            'allowed_with',
            function ($message, $attribute, $rule, $parameters) {
                return str_replace(':others', '\'' . implode('\', \'', $parameters) . '\'', $message);
            }
        );

        $this->app['validator']->replacer(
            'one_of',
            function ($message, $attribute, $rule, $parameters) {
                return str_replace(':others', '\'' . implode('\', \'', $parameters) . '\'', $message);
            }
        );

        $this->app['validator']->replacer(
            'digits',
            function ($message, $attribute, $rule, $parameters) {
                return str_replace(':value', $parameters[0], $message);
            }
        );

        $this->app['validator']->extend('boolean_only', function ($attribute, $value, $parameters, $validator) {
            return is_bool($value);
        });

        $this->app['validator']->extend('integer_only', function ($attribute, $value, $parameters, $validator) {
            return is_integer($value);
        });

    }
}
