<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StateMachineControllerTest extends TestCase
{
    public static function modThreeInputsProvider(): array
    {
        return [
            'good data 1' => ['1000011111'],
            'good data 2' => ['110'],
            'good data 3' => ['111111111111110000001'],
            'bad data' => ['x111115'],
        ];
    } 
    public function testIndexPage(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /* NOTE: the docblock @dataProvider declaration has exist because on WSL2 
       the PHP attribute doesn't work */
    /** @dataProvider modThreeInputsProvider */
    #[DataProvider('modThreeInputsProvider')]
    public function testCalculation($inputString): void
    {
        $response = $this->post('/modthree', ['binaryInput' => $inputString]);
        $this->assertSame(intval($response->getContent()), (bindec($inputString)%3));
    }
}
