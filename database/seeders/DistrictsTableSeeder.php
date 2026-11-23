<?php

namespace Database\Seeders;

use App\Models\State;
use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tamil Nadu Districts (Comprehensive)
        $tamilNadu = State::where('code', 'TN')->first();
        if ($tamilNadu) {
            $tnDistricts = [
                'Ariyalur', 'Chengalpattu', 'Chennai', 'Coimbatore', 'Cuddalore', 'Dharmapuri',
                'Dindigul', 'Erode', 'Kallakurichi', 'Kanchipuram', 'Kanyakumari', 'Karur',
                'Krishnagiri', 'Madurai', 'Mayiladuthurai', 'Nagapattinam', 'Namakkal', 'Nilgiris',
                'Perambalur', 'Pudukkottai', 'Ramanathapuram', 'Ranipet', 'Salem', 'Sivaganga',
                'Tenkasi', 'Thanjavur', 'Theni', 'Thoothukudi', 'Tiruchirappalli', 'Tirunelveli',
                'Tirupathur', 'Tiruppur', 'Tiruvallur', 'Tiruvannamalai', 'Tiruvarur', 'Vellore', 'Viluppuram', 'Virudhunagar'
            ];
            foreach ($tnDistricts as $district) {
                District::create([
                    'state_id' => $tamilNadu->id,
                    'name' => $district,
                    'status' => 'active'
                ]);
            }
        }

        // Karnataka Districts (Major)
        $karnataka = State::where('code', 'KA')->first();
        if ($karnataka) {
            $kaDistricts = [
                'Bengaluru Urban', 'Bengaluru Rural', 'Mysuru', 'Mangaluru', 'Hubballi-Dharwad',
                'Belagavi', 'Kalaburagi', 'Tumakuru', 'Ballari', 'Vijayapura'
            ];
            foreach ($kaDistricts as $district) {
                District::create([
                    'state_id' => $karnataka->id,
                    'name' => $district,
                    'status' => 'active'
                ]);
            }
        }

        // Maharashtra Districts (Major)
        $maharashtra = State::where('code', 'MH')->first();
        if ($maharashtra) {
            $mhDistricts = [
                'Mumbai City', 'Mumbai Suburban', 'Pune', 'Nagpur', 'Thane', 'Nashik',
                'Aurangabad', 'Solapur', 'Amravati', 'Kolhapur'
            ];
            foreach ($mhDistricts as $district) {
                District::create([
                    'state_id' => $maharashtra->id,
                    'name' => $district,
                    'status' => 'active'
                ]);
            }
        }

        // Delhi
        $delhi = State::where('code', 'DL')->first();
        if ($delhi) {
            $dlDistricts = [
                'Central Delhi', 'East Delhi', 'New Delhi', 'North Delhi', 'North East Delhi',
                'North West Delhi', 'Shahdara', 'South Delhi', 'South East Delhi', 'South West Delhi', 'West Delhi'
            ];
            foreach ($dlDistricts as $district) {
                District::create([
                    'state_id' => $delhi->id,
                    'name' => $district,
                    'status' => 'active'
                ]);
            }
        }

        // Kerala Districts
        $kerala = State::where('code', 'KL')->first();
        if ($kerala) {
            $klDistricts = [
                'Thiruvananthapuram', 'Kollam', 'Pathanamthitta', 'Alappuzha', 'Kottayam',
                'Idukki', 'Ernakulam', 'Thrissur', 'Palakkad', 'Malappuram', 'Kozhikode',
                'Wayanad', 'Kannur', 'Kasaragod'
            ];
            foreach ($klDistricts as $district) {
                District::create([
                    'state_id' => $kerala->id,
                    'name' => $district,
                    'status' => 'active'
                ]);
            }
        }

        // Andhra Pradesh Districts (Major)
        $andhraPradesh = State::where('code', 'AP')->first();
        if ($andhraPradesh) {
            $apDistricts = [
                'Visakhapatnam', 'Vijayawada', 'Guntur', 'Nellore', 'Kurnool',
                'Rajahmundry', 'Tirupati', 'Kakinada', 'Anantapur', 'Kadapa'
            ];
            foreach ($apDistricts as $district) {
                District::create([
                    'state_id' => $andhraPradesh->id,
                    'name' => $district,
                    'status' => 'active'
                ]);
            }
        }

        // Telangana Districts
        $telangana = State::where('code', 'TS')->first();
        if ($telangana) {
            $tsDistricts = [
                'Hyderabad', 'Rangareddy', 'Medchal-Malkajgiri', 'Warangal Urban', 'Warangal Rural',
                'Khammam', 'Nalgonda', 'Karimnagar', 'Nizamabad', 'Mahbubnagar'
            ];
            foreach ($tsDistricts as $district) {
                District::create([
                    'state_id' => $telangana->id,
                    'name' => $district,
                    'status' => 'active'
                ]);
            }
        }

        // Gujarat Districts (Major)
        $gujarat = State::where('code', 'GJ')->first();
        if ($gujarat) {
            $gjDistricts = [
                'Ahmedabad', 'Surat', 'Vadodara', 'Rajkot', 'Bhavnagar',
                'Jamnagar', 'Junagadh', 'Gandhinagar', 'Anand', 'Mehsana'
            ];
            foreach ($gjDistricts as $district) {
                District::create([
                    'state_id' => $gujarat->id,
                    'name' => $district,
                    'status' => 'active'
                ]);
            }
        }

        // West Bengal Districts (Major)
        $westBengal = State::where('code', 'WB')->first();
        if ($westBengal) {
            $wbDistricts = [
                'Kolkata', 'North 24 Parganas', 'South 24 Parganas', 'Howrah', 'Hooghly',
                'Bardhaman', 'Murshidabad', 'Nadia', 'Jalpaiguri', 'Darjeeling'
            ];
            foreach ($wbDistricts as $district) {
                District::create([
                    'state_id' => $westBengal->id,
                    'name' => $district,
                    'status' => 'active'
                ]);
            }
        }

        // Uttar Pradesh Districts (Major)
        $uttarPradesh = State::where('code', 'UP')->first();
        if ($uttarPradesh) {
            $upDistricts = [
                'Lucknow', 'Kanpur Nagar', 'Ghaziabad', 'Agra', 'Meerut',
                'Varanasi', 'Allahabad', 'Bareilly', 'Aligarh', 'Moradabad'
            ];
            foreach ($upDistricts as $district) {
                District::create([
                    'state_id' => $uttarPradesh->id,
                    'name' => $district,
                    'status' => 'active'
                ]);
            }
        }

        // Rajasthan Districts (Major)
        $rajasthan = State::where('code', 'RJ')->first();
        if ($rajasthan) {
            $rjDistricts = [
                'Jaipur', 'Jodhpur', 'Kota', 'Bikaner', 'Ajmer',
                'Udaipur', 'Bhilwara', 'Alwar', 'Bharatpur', 'Sikar'
            ];
            foreach ($rjDistricts as $district) {
                District::create([
                    'state_id' => $rajasthan->id,
                    'name' => $district,
                    'status' => 'active'
                ]);
            }
        }

        // Madhya Pradesh Districts (Major)
        $madhyaPradesh = State::where('code', 'MP')->first();
        if ($madhyaPradesh) {
            $mpDistricts = [
                'Indore', 'Bhopal', 'Jabalpur', 'Gwalior', 'Ujjain',
                'Sagar', 'Dewas', 'Satna', 'Ratlam', 'Rewa'
            ];
            foreach ($mpDistricts as $district) {
                District::create([
                    'state_id' => $madhyaPradesh->id,
                    'name' => $district,
                    'status' => 'active'
                ]);
            }
        }

        // Punjab Districts
        $punjab = State::where('code', 'PB')->first();
        if ($punjab) {
            $pbDistricts = [
                'Ludhiana', 'Amritsar', 'Jalandhar', 'Patiala', 'Bathinda',
                'Mohali', 'Hoshiarpur', 'Gurdaspur', 'Pathankot', 'Moga'
            ];
            foreach ($pbDistricts as $district) {
                District::create([
                    'state_id' => $punjab->id,
                    'name' => $district,
                    'status' => 'active'
                ]);
            }
        }

        // Haryana Districts
        $haryana = State::where('code', 'HR')->first();
        if ($haryana) {
            $hrDistricts = [
                'Faridabad', 'Gurgaon', 'Hisar', 'Rohtak', 'Panipat',
                'Karnal', 'Sonipat', 'Yamunanagar', 'Panchkula', 'Ambala'
            ];
            foreach ($hrDistricts as $district) {
                District::create([
                    'state_id' => $haryana->id,
                    'name' => $district,
                    'status' => 'active'
                ]);
            }
        }
    }
}
