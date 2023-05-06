<?php

namespace Modules\SmpVendor\Forms;

use App\Classes\Hook;
use App\Models\User;
use App\Models\UserAttribute;
use App\Services\SettingsPage;
use App\Services\UserOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserVendorForm extends SettingsPage
{
    protected $identifier = 'ns.user-vendor';

    public function __construct()
    {
        $options = app()->make(UserOptions::class);

        $this->form = [
            'tabs' => Hook::filter('ns-user-vendor-form', [
                'attribute' =>  [
                    'label' => __( 'General' ),
                    'fields' => [
                        [
                            'label' => __( 'First Name' ),
                            'name' => 'first_name',
                            'value' => Auth::user()->attribute->first_name ?? '',
                            'type' => 'text',
                            'description' => __( 'Define what is the user first name. If not provided, the username is used instead.' ),
                        ], [
                            'label' => __( 'Vendor Id' ),
                            'name' => 'vendor_id',
                            'value' => Auth::user()->attribute->second_name ?? '',
                            'type' => 'text',
                            'description' => __( 'Define what is the user second name. If not provided, the username is used instead.' ),
                        ], [
                            'label' => __( 'Theme' ),
                            'name' => 'theme',
                            'value' => Auth::user()->attribute->theme ?? '',
                            'type' => 'select',
                            'options' => Helper::kvToJsOptions([
                                'dark' => __( 'Dark' ),
                                'light' => __( 'Light' ),
                            ]),
                            'description' => __( 'Define what is the theme that applies to the dashboard.' ),
                        ], [
                            'label' => __( 'Avatar' ),
                            'name' => 'avatar_link',
                            'value' => Auth::user()->attribute->avatar_link ?? '',
                            'type' => 'media',
                            'data' => [
                                'user_id' => Auth::id(),
                                'type' => 'url',
                            ],
                            'description' => __( 'Define the image that should be used as an avatar.' ),
                        ], [
                            'label' => __( 'Language' ),
                            'name' => 'language',
                            'value' => Auth::user()->attribute->language ?? '',
                            'type' => 'select',
                            'options' => Helper::kvToJsOptions( config( 'nexopos.languages' ) ),
                            'description' => __( 'Choose the language for the current account.' ),
                        ],
                    ],
                ],
            ]),
        ];
    }

    public function saveForm(Request $request)
    {
        ns()->restrict([ 'manage.vendor' ]);

        $validator = Validator::make($request->input('security'), []);

        $results = [];
        $results[] = $this->processCredentials($request, $validator);
        $results[] = $this->processOptions($request);
        $results[] = $this->processAttribute($request);
        $results = collect($results)->filter(fn ($result) => ! empty($result))->values();

        return [
            'status' => 'success',
            'message' => __('The profile has been successfully saved.'),
            'data' => compact('results', 'validator'),
        ];
    }

    public function processAttribute($request)
    {
        $allowedInputs = collect($this->form[ 'tabs' ][ 'attribute' ][ 'fields' ])
            ->map(fn ($field) => $field[ 'name' ])
            ->toArray();

        if (! empty($allowedInputs)) {
            $user = UserAttribute::where('user_id', Auth::user()->id)
                ->firstOrNew([
                    'user_id' => Auth::id(),
                ]);

            foreach ($request->input('attribute') as $key => $value) {
                if (in_array($key, $allowedInputs)) {
                    $user->$key = strip_tags($value);
                }
            }

            $user->save();

            return [
                'status' => 'success',
                'message' => __('The user attribute has been saved.'),
            ];
        }

        return [];
    }

    public function processOptions($request)
    {
        /**
         * @var UserOptions
         */
        $userOptions = app()->make(UserOptions::class);

        if ($request->input('options')) {
            foreach ($request->input('options') as $field => $value) {
                if (! in_array($field, [ 'password', 'old_password', 'password_confirm' ])) {
                    if (empty($value)) {
                        $userOptions->delete($field);
                    } else {
                        $userOptions->set($field, $value);
                    }
                }
            }

            return [
                'status' => 'success',
                'message' => __('The options has been successfully updated.'),
            ];
        }

        return [];
    }

    public function processCredentials($request, $validator)
    {
        if (! empty($request->input('security.old_password'))) {
            if (! Hash::check($request->input('security.old_password'), Auth::user()->password)) {
                $validator->errors()->add('security.old_password', __('Wrong password provided'));

                return  [
                    'status' => 'failed',
                    'message' => __('Wrong old password provided'),
                ];
            } else {
                $user = User::find(Auth::id());
                $user->password = Hash::make($request->input('security.password'));
                $user->save();

                return [
                    'status' => 'success',
                    'message' => __('Password Successfully updated.'),
                ];
            }
        }

        return [];
    }
}