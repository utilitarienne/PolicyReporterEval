<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StateMachineService;
use Illuminate\View\View;
use Throwable;

/**
 * Minimal controller for testing the StateMachineService
**/
class StateMachineController extends Controller
{
    /**
     * Generates the main test page
     * 
     * @return View
    **/
    public function index(): View
    {
        return view('welcome');
    }

    /**
     * Calculates the result of user input using the Mod-Three FSM
     * 
     * @param Request $request
     * @return string
    **/
    public function actionCalculate(Request $request): string
    {
        $processInput = $request->input('binaryInput');
        if(!($processInput === null)) {
            $modThreeFSM = $this->_buildModThreeStateMachine();
            try {
                $FSMOutput =  $modThreeFSM->processInput($processInput);
            } catch (Throwable $e) {
                return '<strong class="text-red-700">Error!</span>';
            }
            return $FSMOutput;
        } else {
            return '<strong class="text-red-700">Error!</span>';
        }
    }

    /**
     * Uses the StateMachineService to build a Mod-Three FSM
     * 
     * @return StateMachineService
    **/
    private function _buildModThreeStateMachine()
    {
        $inputAlphabet = [0, 1];
        $statesAvailable = [
            'S0' => [ 'output' => 0 ],
            'S1' => [ 'output' => 1 ],
            'S2' => [ 'output' => 2 ],
        ];
        $stateInitial = 'S0';
        $stateTransitions = [
            'S0' => [
                '0' => 'S0',
                '1' => 'S1'
            ],
            'S1' => [
                '0' => 'S2',
                '1' => 'S0',
            ],
            'S2' => [
                '0' => 'S1',
                '1' => 'S2',
            ],
        ];

        $modThreeFSM = new StateMachineService(
            $inputAlphabet,
            $statesAvailable,
            $stateInitial,
            $stateTransitions
        );
        
        return $modThreeFSM;
    }

}
