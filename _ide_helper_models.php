<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string|null $fets_no
 * @property string|null $property_no
 * @property string|null $to_receiver
 * @property string $to_office
 * @property string|null $remarks
 * @property string $status
 * @property string|null $rejected_remarks
 * @property int|null $verified_by
 * @property int|null $rejected_by
 * @property int|null $approved_by
 * @property string|null $file_name
 * @property string|null $file_path
 * @property array|null $form_data
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approver
 * @property-read \App\Models\User|null $submitter
 * @property-read \App\Models\User|null $verifier
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument query()
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereFetsNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereFormData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument wherePropertyNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereRejectedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereRejectedRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereToOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereToReceiver($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsDocument whereVerifiedBy($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperFetsDocument {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $fets_no
 * @property string $property_no
 * @property string $action
 * @property string $actor
 * @property string $actor_role
 * @property string|null $remarks
 * @property string $created_at
 * @method static \Illuminate\Database\Eloquent\Builder|FetsLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FetsLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FetsLog query()
 * @method static \Illuminate\Database\Eloquent\Builder|FetsLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsLog whereActor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsLog whereActorRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsLog whereFetsNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsLog wherePropertyNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FetsLog whereRemarks($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperFetsLog {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $type
 * @property int $total
 * @property int $processed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ImportProgress newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ImportProgress newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ImportProgress query()
 * @method static \Illuminate\Database\Eloquent\Builder|ImportProgress whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportProgress whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportProgress whereProcessed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportProgress whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportProgress whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ImportProgress whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperImportProgress {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $FUND_CODE
 * @property string|null $PROPERTY_STATUS
 * @property string|null $ARTICLE_DESCRIPTION
 * @property string|null $GENERAL_DESCRIPTION
 * @property string|null $SERIAL_NO
 * @property string|null $PROPERTY_NO
 * @property string|null $PAR_NO
 * @property string|null $PAR_DATE
 * @property string|null $UNIT
 * @property string|null $QTY
 * @property string|null $ACQUISITION_COST
 * @property string|null $ACQUISITION_DATE
 * @property string|null $RECEIVER
 * @property string|null $SUBPAR
 * @property string|null $ACCOUNT_CODE
 * @property string|null $WARRANTY
 * @property string|null $OFFICE
 * @property string|null $FOUND_IN_STATION
 * @property string|null $LABELLED
 * @property string|null $DPO_REMARKS
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory query()
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereACCOUNTCODE($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereACQUISITIONCOST($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereACQUISITIONDATE($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereARTICLEDESCRIPTION($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereDPOREMARKS($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereFOUNDINSTATION($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereFUNDCODE($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereGENERALDESCRIPTION($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereLABELLED($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereOFFICE($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory wherePARDATE($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory wherePARNO($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory wherePROPERTYNO($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory wherePROPERTYSTATUS($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereQTY($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereRECEIVER($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereSERIALNO($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereSUBPAR($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereUNIT($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Inventory whereWARRANTY($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperInventory {}
}

namespace App\Models{
/**
 * Class User
 *
 * @mixin \Spatie\Permission\Traits\HasRoles
 * @method \Illuminate\Support\Collection getRoleNames()
 * @method \Spatie\Permission\Models\Role|\Spatie\Permission\Models\Permission assignRole(...$roles)
 * @method bool hasRole(string|array|\Spatie\Permission\Models\Role $roles)
 * @method bool hasAnyRole(string|array|\Spatie\Permission\Models\Role $roles)
 * @method bool hasAllRoles(array|\Spatie\Permission\Models\Role ...$roles)
 * @method \Illuminate\Support\Collection getPermissionNames()
 * @method bool hasPermissionTo(string|\Spatie\Permission\Models\Permission $permission, string|null $guardName = null)
 * @method $this givePermissionTo(...$permissions)
 * @method $this revokePermissionTo(...$permissions)
 * @method bool hasDirectPermission(string|\Spatie\Permission\Models\Permission $permission)
 * @property int $id
 * @property string $fullname
 * @property string $username
 * @property string $company_id
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property mixed $password
 * @property string $employee_status
 * @property string $office
 * @property string $region
 * @property string $province
 * @property string $municipality
 * @property string $access_level
 * @property string $activated
 * @property string $locked_status
 * @property string $deleted_status
 * @property \Illuminate\Support\Carbon|null $archived_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $two_factor_code
 * @property \Illuminate\Support\Carbon|null $two_factor_expires_at
 * @property bool $two_factor_enabled
 * @property string|null $deleted_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Illuminate\Database\Eloquent\Builder|User active()
 * @method static \Illuminate\Database\Eloquent\Builder|User archived()
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAccessLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereActivated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereArchivedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmployeeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLockedStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMunicipality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereOffice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProvince($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutRole($roles, $guard = null)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

