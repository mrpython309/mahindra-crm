<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use App\Models\State;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedStates();
        $this->seedDistricts();
        $this->seedCities();
    }

    private function seedStates(): void
    {
        $states = [
            ['name' => 'Andhra Pradesh', 'code' => 'AP', 'status' => 'active'],
            ['name' => 'Arunachal Pradesh', 'code' => 'AR', 'status' => 'active'],
            ['name' => 'Assam', 'code' => 'AS', 'status' => 'active'],
            ['name' => 'Bihar', 'code' => 'BR', 'status' => 'active'],
            ['name' => 'Chhattisgarh', 'code' => 'CG', 'status' => 'active'],
            ['name' => 'Goa', 'code' => 'GA', 'status' => 'active'],
            ['name' => 'Gujarat', 'code' => 'GJ', 'status' => 'active'],
            ['name' => 'Haryana', 'code' => 'HR', 'status' => 'active'],
            ['name' => 'Himachal Pradesh', 'code' => 'HP', 'status' => 'active'],
            ['name' => 'Jharkhand', 'code' => 'JH', 'status' => 'active'],
            ['name' => 'Karnataka', 'code' => 'KA', 'status' => 'active'],
            ['name' => 'Kerala', 'code' => 'KL', 'status' => 'active'],
            ['name' => 'Madhya Pradesh', 'code' => 'MP', 'status' => 'active'],
            ['name' => 'Maharashtra', 'code' => 'MH', 'status' => 'active'],
            ['name' => 'Manipur', 'code' => 'MN', 'status' => 'active'],
            ['name' => 'Meghalaya', 'code' => 'ML', 'status' => 'active'],
            ['name' => 'Mizoram', 'code' => 'MZ', 'status' => 'active'],
            ['name' => 'Nagaland', 'code' => 'NL', 'status' => 'active'],
            ['name' => 'Odisha', 'code' => 'OD', 'status' => 'active'],
            ['name' => 'Punjab', 'code' => 'PB', 'status' => 'active'],
            ['name' => 'Rajasthan', 'code' => 'RJ', 'status' => 'active'],
            ['name' => 'Sikkim', 'code' => 'SK', 'status' => 'active'],
            ['name' => 'Tamil Nadu', 'code' => 'TN', 'status' => 'active'],
            ['name' => 'Telangana', 'code' => 'TS', 'status' => 'active'],
            ['name' => 'Tripura', 'code' => 'TR', 'status' => 'active'],
            ['name' => 'Uttar Pradesh', 'code' => 'UP', 'status' => 'active'],
            ['name' => 'Uttarakhand', 'code' => 'UK', 'status' => 'active'],
            ['name' => 'West Bengal', 'code' => 'WB', 'status' => 'active'],
            // Union Territories
            ['name' => 'Andaman and Nicobar Islands', 'code' => 'AN', 'status' => 'active'],
            ['name' => 'Chandigarh', 'code' => 'CH', 'status' => 'active'],
            ['name' => 'Dadra and Nagar Haveli and Daman and Diu', 'code' => 'DD', 'status' => 'active'],
            ['name' => 'Delhi', 'code' => 'DL', 'status' => 'active'],
            ['name' => 'Jammu and Kashmir', 'code' => 'JK', 'status' => 'active'],
            ['name' => 'Ladakh', 'code' => 'LA', 'status' => 'active'],
            ['name' => 'Lakshadweep', 'code' => 'LD', 'status' => 'active'],
            ['name' => 'Puducherry', 'code' => 'PY', 'status' => 'active'],
        ];

        foreach ($states as $state) {
            State::create($state);
        }
    }

    private function seedDistricts(): void
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

    private function seedCities(): void
    {
        $this->seedTamilNaduCities();
        $this->seedOtherMajorCities();
    }

    private function seedTamilNaduCities(): void
    {
        $tamilNadu = State::where('code', 'TN')->first();
        if (!$tamilNadu) {
            return;
        }

        // Chennai District
        $chennai = District::where('state_id', $tamilNadu->id)->where('name', 'Chennai')->first();
        if ($chennai) {
            $cities = ['Adyar', 'Ambattur', 'Anna Nagar', 'Egmore', 'Guindy', 'Kodambakkam', 'Mylapore', 'Nungambakkam', 'Perambur', 'Purasawalkam', 'T. Nagar', 'Tambaram', 'Velachery'];
            foreach ($cities as $city) {
                City::create(['district_id' => $chennai->id, 'name' => $city, 'status' => 'active']);
            }
        }

        // Coimbatore District
        $coimbatore = District::where('state_id', $tamilNadu->id)->where('name', 'Coimbatore')->first();
        if ($coimbatore) {
            $cities = ['Coimbatore', 'Pollachi', 'Valparai', 'Mettupalayam', 'Sulur', 'Annur', 'Karamadai'];
            foreach ($cities as $city) {
                City::create(['district_id' => $coimbatore->id, 'name' => $city, 'status' => 'active']);
            }
        }

        // Madurai District
        $madurai = District::where('state_id', $tamilNadu->id)->where('name', 'Madurai')->first();
        if ($madurai) {
            $cities = ['Madurai', 'Usilampatti', 'Melur', 'Vadipatti', 'Thirumangalam'];
            foreach ($cities as $city) {
                City::create(['district_id' => $madurai->id, 'name' => $city, 'status' => 'active']);
            }
        }

        // Tiruchirappalli District
        $trichy = District::where('state_id', $tamilNadu->id)->where('name', 'Tiruchirappalli')->first();
        if ($trichy) {
            $cities = ['Tiruchirappalli', 'Srirangam', 'Lalgudi', 'Manapparai', 'Thuraiyur'];
            foreach ($cities as $city) {
                City::create(['district_id' => $trichy->id, 'name' => $city, 'status' => 'active']);
            }
        }

        // Salem District
        $salem = District::where('state_id', $tamilNadu->id)->where('name', 'Salem')->first();
        if ($salem) {
            $cities = ['Salem', 'Mettur', 'Attur', 'Edappadi', 'Sankari', 'Omalur'];
            foreach ($cities as $city) {
                City::create(['district_id' => $salem->id, 'name' => $city, 'status' => 'active']);
            }
        }

        // Tirunelveli District
        $tirunelveli = District::where('state_id', $tamilNadu->id)->where('name', 'Tirunelveli')->first();
        if ($tirunelveli) {
            $cities = ['Tirunelveli', 'Palayamkottai', 'Sankarankovil', 'Ambasamudram', 'Nanguneri'];
            foreach ($cities as $city) {
                City::create(['district_id' => $tirunelveli->id, 'name' => $city, 'status' => 'active']);
            }
        }

        // Vellore District
        $vellore = District::where('state_id', $tamilNadu->id)->where('name', 'Vellore')->first();
        if ($vellore) {
            $cities = ['Vellore', 'Katpadi', 'Gudiyatham', 'Ambur', 'Vaniyambadi'];
            foreach ($cities as $city) {
                City::create(['district_id' => $vellore->id, 'name' => $city, 'status' => 'active']);
            }
        }

        // Erode District
        $erode = District::where('state_id', $tamilNadu->id)->where('name', 'Erode')->first();
        if ($erode) {
            $cities = ['Erode', 'Bhavani', 'Gobichettipalayam', 'Sathyamangalam', 'Perundurai'];
            foreach ($cities as $city) {
                City::create(['district_id' => $erode->id, 'name' => $city, 'status' => 'active']);
            }
        }

        // Tiruppur District
        $tiruppur = District::where('state_id', $tamilNadu->id)->where('name', 'Tiruppur')->first();
        if ($tiruppur) {
            $cities = ['Tiruppur', 'Avinashi', 'Dharapuram', 'Kangeyam', 'Udumalpet'];
            foreach ($cities as $city) {
                City::create(['district_id' => $tiruppur->id, 'name' => $city, 'status' => 'active']);
            }
        }

        // Thanjavur District
        $thanjavur = District::where('state_id', $tamilNadu->id)->where('name', 'Thanjavur')->first();
        if ($thanjavur) {
            $cities = ['Thanjavur', 'Kumbakonam', 'Pattukkottai', 'Papanasam', 'Thiruvaiyaru'];
            foreach ($cities as $city) {
                City::create(['district_id' => $thanjavur->id, 'name' => $city, 'status' => 'active']);
            }
        }

        // Kanchipuram District
        $kanchipuram = District::where('state_id', $tamilNadu->id)->where('name', 'Kanchipuram')->first();
        if ($kanchipuram) {
            $cities = ['Kanchipuram', 'Sriperumbudur', 'Uthiramerur', 'Walajabad'];
            foreach ($cities as $city) {
                City::create(['district_id' => $kanchipuram->id, 'name' => $city, 'status' => 'active']);
            }
        }

        // Chengalpattu District
        $chengalpattu = District::where('state_id', $tamilNadu->id)->where('name', 'Chengalpattu')->first();
        if ($chengalpattu) {
            $cities = ['Chengalpattu', 'Madurantakam', 'Cheyyur', 'Thiruporur', 'Pallavaram'];
            foreach ($cities as $city) {
                City::create(['district_id' => $chengalpattu->id, 'name' => $city, 'status' => 'active']);
            }
        }

        // Tiruvallur District
        $tiruvallur = District::where('state_id', $tamilNadu->id)->where('name', 'Tiruvallur')->first();
        if ($tiruvallur) {
            $cities = ['Tiruvallur', 'Poonamallee', 'Avadi', 'Ponneri', 'Gummidipoondi'];
            foreach ($cities as $city) {
                City::create(['district_id' => $tiruvallur->id, 'name' => $city, 'status' => 'active']);
            }
        }
    }

    private function seedOtherMajorCities(): void
    {
        // Karnataka - Bengaluru Urban
        $karnataka = State::where('code', 'KA')->first();
        if ($karnataka) {
            $bengaluru = District::where('state_id', $karnataka->id)->where('name', 'Bengaluru Urban')->first();
            if ($bengaluru) {
                $cities = ['Bangalore', 'Yelahanka', 'Whitefield', 'Electronic City', 'Jayanagar', 'Koramangala', 'Indiranagar'];
                foreach ($cities as $city) {
                    City::create(['district_id' => $bengaluru->id, 'name' => $city, 'status' => 'active']);
                }
            }
        }

        // Maharashtra - Mumbai
        $maharashtra = State::where('code', 'MH')->first();
        if ($maharashtra) {
            $mumbai = District::where('state_id', $maharashtra->id)->where('name', 'Mumbai City')->first();
            if ($mumbai) {
                $cities = ['Colaba', 'Fort', 'Byculla', 'Dadar', 'Parel', 'Worli', 'Bandra', 'Andheri'];
                foreach ($cities as $city) {
                    City::create(['district_id' => $mumbai->id, 'name' => $city, 'status' => 'active']);
                }
            }

            $pune = District::where('state_id', $maharashtra->id)->where('name', 'Pune')->first();
            if ($pune) {
                $cities = ['Pune', 'Pimpri-Chinchwad', 'Khadki', 'Hadapsar', 'Hinjewadi'];
                foreach ($cities as $city) {
                    City::create(['district_id' => $pune->id, 'name' => $city, 'status' => 'active']);
                }
            }
        }

        // Delhi
        $delhi = State::where('code', 'DL')->first();
        if ($delhi) {
            $newDelhi = District::where('state_id', $delhi->id)->where('name', 'New Delhi')->first();
            if ($newDelhi) {
                $cities = ['Connaught Place', 'Chanakyapuri', 'Diplomatic Enclave', 'Lodhi Road'];
                foreach ($cities as $city) {
                    City::create(['district_id' => $newDelhi->id, 'name' => $city, 'status' => 'active']);
                }
            }
        }

        // Kerala - Ernakulam
        $kerala = State::where('code', 'KL')->first();
        if ($kerala) {
            $ernakulam = District::where('state_id', $kerala->id)->where('name', 'Ernakulam')->first();
            if ($ernakulam) {
                $cities = ['Kochi', 'Aluva', 'Perumbavoor', 'Kalamassery', 'Tripunithura'];
                foreach ($cities as $city) {
                    City::create(['district_id' => $ernakulam->id, 'name' => $city, 'status' => 'active']);
                }
            }
        }

        // Telangana - Hyderabad
        $telangana = State::where('code', 'TS')->first();
        if ($telangana) {
            $hyderabad = District::where('state_id', $telangana->id)->where('name', 'Hyderabad')->first();
            if ($hyderabad) {
                $cities = ['Hyderabad', 'Secunderabad', 'Kukatpally', 'Madhapur', 'Gachibowli', 'Banjara Hills'];
                foreach ($cities as $city) {
                    City::create(['district_id' => $hyderabad->id, 'name' => $city, 'status' => 'active']);
                }
            }
        }
    }
}
