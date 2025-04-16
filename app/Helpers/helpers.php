<?php
use App\Models\Setting;
use App\Models\GhlAuth;
use App\Models\GhlUser;
use App\Models\CustomField;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
function supersetting($key, $default = '')
{
        $setting = Setting::where(['user_id' =>1, 'key' => $key])->first();
        $value = $setting->value ?? $default;
          return $value;
}
if (!function_exists('getActions')) {
    /**
     * Generate action buttons for DataTables
     *
     * @param array $actions
     * @param string $route
     * @param int $id
     * @return string
     */
    function getActions(array $actions, string $route, int $id)
    {
        $html = '';

        if (isset($actions['edit']) && $actions['edit']) {
            $editUrl = route($route . '.edit', ['user' => $id]);
            $html .= '<a href="' . $editUrl . '" class="btn btn-sm btn-primary">Edit</a> ';
        }

        if (isset($actions['delete']) && $actions['delete']) {
            $deleteUrl = route($route . '.destroy', ['user' => $id]);
            $html .= '<a href="' . $deleteUrl . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">Delete</a>';
        }

        return $html;
    }
}

//This Function give the user id of the Current logged in User
function isSuperAdmin()
{
    return auth()->user()->role == 1 || is_role() == 'admin';
}

function login_id($id = "")
{
    if (!empty($id)) {
        return $id;
    }

    if (auth()->user()) {
        $id = auth()->user()->id;
    } elseif (session('uid')) {
        $id = session('uid');
    } elseif (Cache::has('user_ids321')) {
        $id = Cache::get('user_ids321');
    }

    return $id;
}

// function superAdmin()
// {
//    return 1;
// }
//This Function give the role of the Current logged in User

function is_role($user=null)
{
    if(!$user){
        if(auth()->user()){
            $user= auth()->user();
        }
    }

    if($user){
        if ($user->role == 1) {
            return 'admin';
        } elseif ($user->role == 2) {
            return 'company';
        } else {
            return 'user';
        }
    }

    return null;
}


function save_settings($key, $value = '', $userid = null)
{
    if (is_null($userid)) {
        $user_id = login_id();
    } else {
        $user_id = $userid;
    }
    // 'user_id' => $user_id;
    $setting = Setting::updateOrCreate(
        ['key' => $key],
        [
            'value' => $value,
            'user_id' => $user_id,
            'key' => $key,
        ]
    );
    return $setting;
}

function  uploadFile($file, $path, $name)
{
   // Append the original file extension to the custom name
   $name = $name . '.' . $file->getClientOriginalExtension();

   // Move the file to the public folder with the specified path
   $file->move(public_path($path), $name);
   return $path . '/' . $name;
}

function loginUser()
{
    return auth()->user() ?? null;

}

function isCompanyUser()
{
    $user = auth()->user(); // Getting the authenticated user
    return $user && $user->role == 2 ? 'hidden' : '';
}

function ghlUser($data){

foreach($data->users as $userData){
    $ghlUser = GhlUser::where('ghl_user_id',$userData->id)->first();
    if (!$ghlUser) {
        $ghlUser = GhlUser::create([
            'ghl_user_id' => $userData->id,
            'ghl_user_name' => $userData->name,
            'ghl_user_email' => $userData->email,
            'location_id' => $userData->roles->locationIds[0],
        ]);
        //dd($ghlUser);
    } else {
        $ghlUser->update([
            'ghl_user_email' => $userData->name,
            'ghl_user_email' => $userData->email,
        ]);
    }
}
}
function ContactField($data) {
    //dd($data);
    $all_custom_fields = $data->pluck('ghl_contact_id');
    //dd($all_custom_fields);
    $custom_fields = DB::table('custom_fields')
        ->select('cf_value', DB::raw('COUNT(*) as count'))
        ->where('cf_key', 'contact.status')
        ->whereIn('cf_value', ['Appointment Confirmed', 'Appointment Show', 'Appointment Re-Scheduled', 'Appt. Cancelled', 'Appointment Booked'])
        ->whereIn('custom_fields.ghl_contact_id', $all_custom_fields)
        ->groupBy('cf_value')
        ->get();
        //dd($custom_fields);
    return $custom_fields;
}

function productionGraph($contactQuery)
{
    // Get the current year and month
    $currentYear = Carbon::now()->year;
    $currentMonth = Carbon::now()->month;
    $monthName = Carbon::now()->format('F');

    // Initialize variables to store results
    $data = [];
    $totalPremium = 0;
    $healthCount = 0;
    $privateCount = 0;
    $contactPremiumQuery=[];
    // Loop through each contact model in the query
    foreach ($contactQuery as $contactQueries) {
        // Get the premium values for each contact
        $contactPremiumQuery = $contactQueries::join('custom_fields', 'contacts.ghl_contact_id', '=', 'custom_fields.ghl_contact_id')
            ->whereNotNull('custom_fields.cf_value')
            ->where('custom_fields.cf_key', 'contact.2025_monthly_premium')
            ->whereYear('custom_fields.created_at', $currentYear)
            ->whereMonth('custom_fields.created_at', $currentMonth)
            ->select('custom_fields.cf_value')
            ->get();

        // Sum the premiums from the result of the query


        // Get the health enrollment data for each contact
        $contactHealthEnrollment = $contactQueries::join('custom_fields', 'contacts.ghl_contact_id', '=', 'custom_fields.ghl_contact_id')
            ->whereNotNull('custom_fields.cf_value')
            ->where('custom_fields.cf_key', 'contact.2025_carrier_name')
            ->whereYear('custom_fields.created_at', $currentYear)
            ->whereMonth('custom_fields.created_at', $currentMonth)
            ->select('custom_fields.cf_value')
            ->get();

        // Count the health enrollments
        $healthCount = $contactHealthEnrollment->count();
        // Get the private enrollment data for each contact

        $contactPrivateEnrollment = $contactQueries::join('custom_fields as cf3', 'contacts.ghl_contact_id', '=', 'cf3.ghl_contact_id')
            ->whereNotNull('cf3.ghl_contact_id')
            ->where('cf3.contact_id', '!=', '')
            ->whereYear('cf3.created_at', $currentYear)
            ->whereMonth('cf3.created_at', $currentMonth)
            ->select('cf3.cf_value')
            ->get();
        // Count the private enrollments
        $privateCount = $contactPrivateEnrollment->count();
    }

    foreach ($contactPremiumQuery as $customField) {
        if (is_numeric($customField->cf_value)) {
            $totalPremium += $customField->cf_value;
        }
    }
    // Populate the data array with health, private, and premium info
    $data[$currentYear][$monthName]['health'] = $healthCount > 0 ? $healthCount : 0;
    $data[$currentYear][$monthName]['private'] = $privateCount > 0 ? $privateCount : 0;
    $data[$currentYear][$monthName]['annual_premium'] = $totalPremium > 0 ? $totalPremium * 12 : 0;
    //dd($data);
    return $data;
}

function CallDataShowByMonth($contactQuery,$callQuery){
    $callData = [];

// Count calls for the current month
$callData['countCall'] = (clone $callQuery)
    ->leftJoin('users', 'calls.location_id', '=', 'users.location_id')
    ->whereMonth('calls.created_at', Carbon::now()->month)
    ->count();

// Count referrals with 'paid' status for the current month
$callData['referral_amount_paid'] = (clone $contactQuery)
    ->join('custom_fields as cf', 'contacts.ghl_contact_id', '=', 'cf.contact_id')
    ->where('cf.cf_value', 'paid')
    ->where('cf.cf_key', 'contact.referral_amount_paid')
    ->whereMonth('contacts.created_at', Carbon::now()->month)
    ->count();

// Count contacts with 'closed' status for the current month
$contactClose = (clone $contactQuery)
    ->join('custom_fields as cf5', 'contacts.ghl_contact_id', '=', 'cf5.contact_id') // Use alias cf2 for the second join
    ->where('cf5.cf_value', 'closed')
    ->where('cf5.cf_key', 'contact.status')
    ->whereMonth('contacts.created_at', Carbon::now()->month)
    ->count();

// Calculate appointment conversion or other data
if ($callData['referral_amount_paid'] == 0) {
    $callData['appointmentConversion'] = 0; // Prevent division by zero
} else {
    $callData['appointmentConversion'] = min(($contactClose / $callData['referral_amount_paid']) * 100, 100);
}

return $callData;

}

function printHello()
{
    return 'Hello World';
}



