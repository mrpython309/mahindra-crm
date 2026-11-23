<?php

namespace Database\Seeders;

use App\Models\State;
use App\Models\District;
use App\Models\City;
use Illuminate\Database\Seeder;

class CitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tamil Nadu Cities (Comprehensive for major districts)
        $this->seedTamilNaduCities();
        
        // Other major cities
        $this->seedOtherMajorCities();
    }

    private function seedTamilNaduCities()
    {
        $tamilNadu = State::where('code', 'TN')->first();
        if (!$tamilNadu) return;

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

        // Nilgiris District
        $nilgiris = District::where('state_id', $tamilNadu->id)->where('name', 'Nilgiris')->first();
        if ($nilgiris) {
            $cities = ['Ooty', 'Coonoor'];
            foreach ($cities as $city) {
                City::create(['district_id' => $nilgiris->id, 'name' => $city, 'status' => 'active']);
            }
        }
    }

    private function seedOtherMajorCities()
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
