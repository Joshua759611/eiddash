<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CovidKit extends BaseModel
{
    public function computekitsUsed($tests)
    {
    	if ($tests == 0 || $this->calculated_pack_size == NULL)
    		return 0;
    	
    	return (int)round(($tests + (($tests/94) * 2))/$this->calculated_pack_size);
    	// return $tests;
    }

    public function platform()
    {
        return $this->belongsTo(Machine::class, 'machine', 'id');
    }

    public function beginingbalance()
    {
    	$balance = 0;
    	$last_week = $this->getPreviousWeek();
    	$last_week_consumption = CovidConsumption::whereDate('start_of_week', $last_week->week_start)->get();
    	
    	if (!$last_week_consumption->isEmpty()){
    		$details = $last_week_consumption->first()->details->where('kit_id', $this->id);
    		if (!$details->isEmpty()){
    			$balance = $details->first()->ending;
    		}
    	}
    								
    	return $balance;
    }

    private function getPreviousWeek()
    {
    	$date = strtotime('-14 days', strtotime(date('Y-m-d')));
    	return $this->getStartAndEndDate(date('W', $date),
    							date('Y', $date));
    }

    private function getStartAndEndDate($week, $year) {
		$dto = new \DateTime();
		$dto->setISODate($year, $week);
		$ret['week_start'] = $dto->format('Y-m-d');
		$dto->modify('+6 days');
		$ret['week_end'] = $dto->format('Y-m-d');
		$ret['week'] = date('W', strtotime($ret['week_start']));
		return (object)$ret;
	}

    public function updateAbbott()
    {
        $updates = [
            ['material_no' => '09N77-090', 'pack_size' => 96, 'calculated_pack_size' => 96],
            ['material_no' => '09N77-080', 'pack_size' => 16, 'calculated_pack_size' => 2],
            ['material_no' => '4J71-10', 'pack_size' => 2304, 'calculated_pack_size' => 841],
            ['material_no' => '4J71-17', 'pack_size' => 2304, 'calculated_pack_size' => 96],
            ['material_no' => '4J71-80', 'pack_size' => 150, 'calculated_pack_size' => 1],
            ['material_no' => '4J71-30', 'pack_size' => 32, 'calculated_pack_size' => 3],
            ['material_no' => '4J71-20', 'pack_size' => 2000, 'calculated_pack_size' => 96],
            ['material_no' => '4J71-60', 'pack_size' => 90, 'calculated_pack_size' => 6],
            ['material_no' => '4J71-45', 'pack_size' => 50, 'calculated_pack_size' => 1],
            ['material_no' => '4J71-70', 'pack_size' => 20, 'calculated_pack_size' => 1],
            ['material_no' => '4J71-75', 'pack_size' => 100, 'calculated_pack_size' => 1],
            ['material_no' => '06K12-24',  'pack_size' => 96, 'calculated_pack_size' => 96]
        ];

        foreach ($updates as $key => $updatekit) {
            $kit = $this->where('material_no', $updatekit['material_no'])->first();
            if ($kit) {
                $kit->pack_size = $updatekit['pack_size'];
                $kit->calculated_pack_size = $updatekit['calculated_pack_size'];
                $kit->save();
            }
        }
    }

    public function adjustKits()
    {
        $manual = [
            ['material_no' => 'M1', 'product_description' => 'DAAN Extraction Kits', 'pack_size' => 240, 'calculated_pack_size' => 240, 'type' => 'Manual', 'unit' => 'tips', 'manual_machine' => 1],
            ['material_no' => 'M2', 'product_description' => 'SARS-Cov2 Primers and probes', 'pack_size' => 96, 'calculated_pack_size' => 96, 'type' => 'Manual', 'unit' => 'tips', 'manual_machine' => 1],
            ['material_no' => 'M20', 'product_description' => 'SD Biosensor Extraction Kits', 'pack_size' => 300, 'calculated_pack_size' => 300, 'type' => 'Manual', 'unit' => 'tips', 'manual_machine' => 2],
            ['material_no' => 'M30', 'product_description' => 'Sun Sure  Extraction Kits', 'pack_size' => 4000, 'calculated_pack_size' => 4000, 'type' => 'Manual', 'unit' => 'tips', 'manual_machine' => 3],
            ['material_no' => 'M7', 'product_description' => '200µl Sterile Filtered Pipette tips ',
            'pack_size' => 960, 'calculated_pack_size' => 960, 'type' => 'Consumable', 'unit' => 'tips'],
            ['material_no' => 'M8', 'product_description' => '1000µl Sterile Filtered Pipette tips',
            'pack_size' => 960, 'calculated_pack_size' => 960, 'type' => 'Consumable', 'unit' => 'tips'],
            ['material_no' => 'M5', 'product_description' => '10µl Sterile Filtered Pipette tips',
            'pack_size' => 960, 'calculated_pack_size' => 960, 'type' => 'Consumable', 'unit' => 'tips'],
            ['material_no' => 'M6', 'product_description' => '100µl Sterile Filtered Pipette tips',
            'pack_size' => 960, 'calculated_pack_size' => 960, 'type' => 'Consumable', 'unit' => 'tips'],
            ['material_no' => 'P40', 'product_description' => 'MicroAmp Fast Optical fast Optical 96-well Reaction plate 0.1ml(20)', 'pack_size' => 100, 'calculated_pack_size' => 100, 'type' => 'Consumable', 'unit' => 'pack'],
            ['material_no' => 'P41', 'product_description' => 'Adhesive Plate 96-well Seals', 'pack_size' => 100, 'calculated_pack_size' => 100, 'type' => 'Consumable', 'unit' => 'pack'],
        ];
        foreach ($manual as $key => $kit) {
            $dbkit = CovidKit::where('material_no', $kit['material_no'])->get();
            if ($dbkit->isEmpty()){
                CovidKit::create($kit);
            } else {
                $dbkit = $dbkit->first();
                $dbkit->fill($kit);
                $dbkit->save();
            }
        }

        $deactivated_consumables = [
            ['material_no' => 'P1'],
            ['material_no' => 'P2'],
            ['material_no' => 'M4'],
            ['material_no' => 'M3'],
            ['material_no' => 'P3'],
            ['material_no' => 'P4'],
            ['material_no' => 'P5'],
            ['material_no' => 'P6'],
            ['material_no' => 'P8'],
            ['material_no' => 'P9'],
            ['material_no' => 'P10'],
            ['material_no' => 'P21'],
            ['material_no' => 'P22'],
            ['material_no' => 'P23'],
            ['material_no' => 'P37'],
            ['material_no' => '09N77-001']
        ];
        foreach ($deactivated_consumables as $key => $kit) {
            $dbkit = CovidKit::where('material_no', $kit['material_no'])->get();
            if (!$dbkit->isEmpty()){
                $dbkit->first()->delete();
            }
        }

        return true;
    }
}
