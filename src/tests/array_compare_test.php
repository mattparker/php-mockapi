<?php
/**
 * Created by PhpStorm.
 * User: matt
 * Date: 08/07/14
 * Time: 15:55
 */
require_once __DIR__ . '/loader.php';


class ArrayCompareTest extends PHPUnit_Framework_TestCase {


    private function compare (array $a1, array $a2) {
        return \MockServer\array_same_recursive($a1, $a2);
    }

    public function test_simple_comparison () {

        $answer = $this->compare([], []);
        $this->assertTrue($answer);
    }

    public function test_two_flat_arrays () {

        $answer = $this->compare([1, 2], [1, 2]);
        $this->assertTrue($answer);
    }

    public function test_different_arrays () {
        $this->assertFalse($this->compare([2], [3]));
    }

    public function test_keyed_flat_arrays () {
        $this->assertTrue($this->compare(["a" => 2], ["a" => 2]));
    }

    public function test_keyed_long_flat_arrays () {
        $this->assertTrue($this->compare(["a" => 2, "b" => "kjlkj"], ["b" => "kjlkj", "a" => 2]));
    }

    public function test_deep_arrays () {
        $this->assertTrue($this->compare(["a" => ["c" => 2, "b" => "kjlkj"]], ["a" => ["c" => 2, "b" => "kjlkj"]]));
    }

    public function test_deep_arrays_that_are_different () {
        $this->assertFalse($this->compare(["a" => ["c" => 2, "b" => "nope"]], ["a" => ["c" => 2, "b" => "kjlkj"]]));
    }

    public function test_deep_arrays_with_three_nestings () {
        $a1 = [
            'k' => '34',
            'm' => [
                [
                    'email' => [
                        'email' => 'abc@dfe'
                    ],
                    'thing' => 'yes'
                ]
            ]
        ];
        $a2 = [
            'k' => '34',
            'm' => [
                [
                    'email' => [
                        'email' => 'abc@dfe'
                    ],
                    'thing' => 'yes'
                ]
            ]
        ];
        $this->assertTrue($a1 === $a2, ' are not the same ');
        $this->assertTrue($this->compare($a1, $a2));
    }
}
 