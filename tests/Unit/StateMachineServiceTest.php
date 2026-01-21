<?php 

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\StateMachineService;
use Illuminate\Support\Collection;
use \ErrorException;

final class StateMachineServiceTest extends TestCase
{
    public static function fsmBadDataProvider(): iterable
    {
        yield 'bad initial state' => [
            'inputAlphabet' => [0, 1],
            'statesAvailable' => [
                'S0' => ['output' => 0],
                'S1' => ['output' => 1],
                'S2' => ['output' => 2],
            ],
            'stateInitial' => 'S5',
            'stateTransitions' => [
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
            ],
        ];
        yield 'bad state transition: bad initial state' => [
            'inputAlphabet' => [0, 1],
            'statesAvailable' => [
                'S0' => ['output' => 0],
                'S1' => ['output' => 1],
                'S2' => ['output' => 2],
            ],
            'stateInitial' => 'S0',
            'stateTransitions' => [
                'S0' => [
                    '0' => 'S0',
                    '1' => 'S1'
                ],
                'S5' => [
                    '0' => 'S2',
                    '1' => 'S0',
                ],
                'S2' => [
                    '0' => 'S1',
                    '1' => 'S2',
                ],
            ],
        ];
        yield 'bad state transition: bad input token' => [
            'inputAlphabet' => [0, 1],
            'statesAvailable' => [
                'S0' => ['output' => 0],
                'S1' => ['output' => 1],
                'S2' => ['output' => 2],
            ],
            'stateInitial' => 'S0',
            'stateTransitions' => [
                'S0' => [
                    '0' => 'S0',
                    'x' => 'S1'
                ],
                'S5' => [
                    '0' => 'S2',
                    '1' => 'S0',
                ],
                'S2' => [
                    '0' => 'S1',
                    '1' => 'S2',
                ],
            ],
        ];
        yield 'bad state transition: bad transitioning state' => [
            'inputAlphabet' => [0, 1],
            'statesAvailable' => [
                'S0' => ['output' => 0],
                'S1' => ['output' => 1],
                'S2' => ['output' => 2],
            ],
            'stateInitial' => 'S0',
            'stateTransitions' => [
                'S0' => [
                    '0' => 'S0',
                    '1' => 'S1'
                ],
                'S5' => [
                    '0' => 'S2',
                    '1' => 'S8',
                ],
                'S2' => [
                    '0' => 'S1',
                    '1' => 'S2',
                ],
            ],
        ];
    }
    public static function fsmGoodDataProvider(): iterable
    {
        yield 'good data 1' => [
            'inputAlphabet' => [0, 1],
            'statesAvailable' => [
                'S0' => ['output' => 0],
                'S1' => ['output' => 1],
                'S2' => ['output' => 2],
            ],
            'stateInitial' => 'S0',
            'stateTransitions' => [
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
            ],
            'input' => '110',
            'expectedOutput' => '0',
        ];
        yield 'good data 2' => [
            'inputAlphabet' => ['x', 'y'],
            'statesAvailable' => [
                'S0' => ['output' => 'hello'],
                'S1' => ['output' => 'goodbye'],
            ],
            'stateInitial' => 'S0',
            'stateTransitions' => [
                'S0' => [
                    'x' => 'S0',
                    'y' => 'S1'
                ],
                'S1' => [
                    'x' => 'S1',
                    'y' => 'S0',
                ],
            ],   
            'input' => 'xyxxy',
            'expectedOutput' => 'hello'
        ];
    }

    /* NOTE: the docblock @dataProvider declaration has exist because on WSL2 
       the PHP attribute doesn't work */
    /** @dataProvider fsmBadDataProvider */
    #[DataProvider('fsmBadDataProvider')]
    public function testInstantiateWithBadData(
        $inputAlphabet,
        $statesAvailable,
        $stateInitial,
        $stateTransitions
        ): void
    {
        $this->expectException(ErrorException::class);

        $FSM = new StateMachineService(
            $inputAlphabet,
            $statesAvailable,
            $stateInitial,
            $stateTransitions
        );
    }

    /* NOTE: the docblock @dataProvider declaration has exist because on WSL2 
       the PHP attribute doesn't work */
    /** @dataProvider fsmGoodDataProvider */
    #[DataProvider('fsmGoodDataProvider')]
    public function testInstantiateWithGoodData(
        $inputAlphabet,
        $statesAvailable,
        $stateInitial,
        $stateTransitions,
        $input,
        $expectedOutput
        ): void
    {
        $FSM = new StateMachineService(
            $inputAlphabet,
            $statesAvailable,
            $stateInitial,
            $stateTransitions
        );

        $this->assertInstanceOf(StateMachineService::class, $FSM);
    }

    /* NOTE: the docblock @dataProvider declaration has exist because on WSL2 
       the PHP attribute doesn't work */
    /** @dataProvider fsmGoodDataProvider */
    #[DataProvider('fsmGoodDataProvider')]
    public function testReturnOutput(
        $inputAlphabet,
        $statesAvailable,
        $stateInitial,
        $stateTransitions,
        $input,
        $expectedOutput
        ): void
    {
        $FSM = new StateMachineService(
            $inputAlphabet,
            $statesAvailable,
            $stateInitial,
            $stateTransitions
        );

        $this->assertEquals($FSM->processInput($input), $expectedOutput);
    }






}
