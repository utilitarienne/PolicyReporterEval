<?php

namespace App\Services;

use Illuminate\Support\Collection;
use \ErrorException;
use Throwable;

/**
 * Generic FSM builder service
**/
class StateMachineService
{
    /**
     * Input alphabet
     * 
     * A Collection of single characters 
    **/
    private Collection $inputAlphabet;

    /**
     * List of available states
     * 
     * A Collection, structured as follows:
     * 
     * [
     *   'stateName1' => [ 
     *      'output'    => 'someOutput',
     *      'allowFinal => true,
     *   ],
     *  'stateName2' => [ 
     *      'output'    => 'someOtherOutput',
     *      'allowFinal => true,
     *   ],
     *   'nonFinalStateName' => [
     *      'allowFinal' => false,
     *   ]
     *   …
     * ] 
     * 
     * If a state has output, we consider that it is allowed to be a final state.
     * If it does not, we consider that it is NOT permitted as a final state. 
    **/
    private Collection $statesAvailable;

    /**
     * The initial state of the new FSM
     * 
     * The name of a state (which must be included in the $statesAvailable collection)
    **/
    private string $stateInitial;

    /**
     * The current state of the FSM (after a transition is applied)
     * 
     * The name of a state (which must be included in the $statesAvailable collection)
    **/
    private ?string $stateCurrent = null;

    /**
     * The transition function, expressed as a list of tuples 
     * 
     * A Collection, structured as follows:
     * 
     * [
     *  'currentStateName1' => [ 
     *      'inputAlphabetItem1' => 'stateNameToTransitionTo',
     *      'inputAlphabetItem2' => 'snotherStateNameToTransitionTo',
     *      …
     *  ],
     *  'currentStateName2' => [ 
     *      'inputAlphabetItem1' => 'stateNameToTransitionTo',
     *      'inputAlphabetItem2' => 'snotherStateNameToTransitionTo',
     *      …
     *  ],
     * ] 
    **/
    private Collection $stateTransitions;

    /**
     * Constructor function
     * 
     * @param string|array|Collection $inputAlphabet 
     * @param array|Collection $statesAvailable
     * @param string $stateInitial
     * @param array|Collection $stateTransitions
     * 
     * The constructor needs the input alphabet, the list of possible states,
     * the initial state (which is also set as the current state), and the
     * transition function.
    **/
    public function __construct(
        string|array|Collection $inputAlphabet,
        array|Collection $statesAvailable,
        string $stateInitial,
        array|Collection $stateTransitions
    ) {
        // set input alphabet
        $this->_setInputAlphabet($inputAlphabet);

        // set available states
        $this->_setStatesAvailable($statesAvailable);

        // set initial state
        $this->_setInitialState($stateInitial);

        // set up transitions
        $this->_setStateTransitions($stateTransitions); 
    }

    /**
     * Setter for the input alphabet
     * 
     * @param array|Collection $items
     * @return void
     * 
     * Takes a string, an array, or a Collection and sets the value of $this->inputAlphabet 
     * to the resulting Collection.
    **/
    private function _setInputAlphabet(string|array|Collection $items): void
    {
        // convert to collection if necessary
        if(is_string($items)) {
            $items = collect(str_split($items));
        } elseif (is_array($items)) {
            $items = collect($items)->recursive();
        }

        // if we have any integers or anything, convert them to strings
        $output = $items->map(function(mixed $item) {
            return strval($item);
        });

        // set property
        $this->inputAlphabet = $output;
    }

    /**
     * Setter for the available states
     * 
     * @param array|Collection $items
     * @return void
     * 
     * Takes an array or a Collection, configures whether states are 
     * allowed/disallowed as final states, and sets the value of $this->statesAvailable 
     * to the resulting Collection.
    **/
    private function _setStatesAvailable(array|Collection $items): void
    {
        // convert to collection if necessary
        if (is_array($items)) {
            $items = collect($items)->recursive();
        } else { // otherwise we want to array-ify and then recollect the items recursively just in case
            $items = collect($items->toArray())->recursive();
        }

        // set states to allowed/disallowed as final states
        $output = $items->map(function(Collection $item): Collection {
            if($item->has('output') && !($item->get('output') === null)) {
                $item->put('allowFinal', true);
            } else {
                $item->put('allowFinal', false);
            }

            return $item;
        });

        // set property
        $this->statesAvailable = $output;
    }

    /**
     * Setter for $initialState
     * 
     * @param string $stateName
     * @return void
     * 
     * Takes a string, checks it against our list of available states, 
     * and sets $this->initialState
    **/
    private function _setInitialState(string $stateName): void
    {
        // check that we've been passed a state that's in the list
        if($this->_testForAvailableState($stateName, 'The initial state is invalid') === true) {
            // set property
            $this->stateInitial = $stateName;
        }
    }

    /**
     * Setter for $currentState
     * 
     * @param string $stateName
     * @return void
     * 
     * Takes a string, checks it against our list of available states, 
     * and sets $this->currentState
    **/
    private function _setCurrentState(?string $stateName = null): void
    {
        // if we didn't get a stateName, we're initializing; set to $this->initialState
        if(!$stateName || $stateName === null) {
            $this->stateCurrent = $this->stateInitial;
        // otherwise we need to check if the state is available
        } elseif($this->_testForAvailableState($stateName, "The transition's subsequent state is invalid") === true) {
            // and set our property if so
            $this->stateCurrent = $stateName;
        }
    }

    /**
     * setter for $stateTransitions
     * @param array|Collection $items
     * @return void
     * 
     * Takes an array or Collection representing available transitions, 
     * verifies that all tokens are in the input alphabet and all states are available,
     * and sets $stateTransitions to the resulting Collection
    **/
    private function _setStateTransitions(array|Collection $items): void
    {
        // convert to collection if necessary
        if (is_array($items)) {
            $items = collect($items)->recursive();
        } else { // otherwise we want to array-ify and then recollect the items recursively just in case
            $items = collect($items->toArray())->recursive();
        }

        // double-check our states & alphabet as in-universe
        $items->each(function(Collection $item, string $key): void {
            // check if our initial state exists
            if($this->_testForAvailableState($key, "The transition's initial state is invalid") === true) {
                $item->each(function(string $inner, string|int $innerKey): void {
                    // check whether the token is in the alphabet
                    $this->_testForTokenInAlphabet(strval($innerKey));
                    // check whether the state is available
                    $this->_testForAvailableState($inner, "The transition's subsequent state is invalid");
                });

            }
        });

        // set property
        $this->stateTransitions = $items;
    }

    /**
     * Processes an input string through the created FSM
     * @param string $input
     * @return string
     *
    **/
    public function processInput(string $input): string
    {
        // initialize the FSM by setting current state to initial state
        $this->_setCurrentState();

        // split our input into tokens
        $input = collect(str_split($input));

        // loop through tokens and transition states appropriately
        $input->each(function(string $token): void {
            if($this->_testForTokenInAlphabet($token)) {
                $possibleTransitions = $this->stateTransitions->get($this->stateCurrent);
                if(!$possibleTransitions->has($token)) {
                    throw (new ErrorException($message = 'There is no allowable transition'));
                } else {
                    // set our current state
                    $this->_setCurrentState($possibleTransitions->get($token));
                }
            }
        });

        // evaluate and return the final output
        return $this->_validateOutput();
    }

    /**
     * Checks if a token is in $this->inputAlphabet,
     * throws an exception otherwise
     * 
     * @param string|int $token
     * @return bool
    **/
    private function _testForTokenInAlphabet(string|int $token): bool
    {
        if(!($this->inputAlphabet->contains(strval($token)))) {
            throw (new ErrorException($message = "The token is not in the input alphabet"));
        } else {
            return true;
        }
    }

    /**
     * Checks if a given state name is in $this->statesAvailable,
     * throws an exception otherwise
     * 
     * @param string|int $stateName
     * @param string $message
     * @return bool
    **/
    private function _testForAvailableState(
        string $stateName,
        string $message = 'The provided state name is invalid'): bool
    {
        if(!($this->statesAvailable->has($stateName))) {
            throw (new ErrorException($message));
        } else {
            return true;
        }
    }

    /**
     * Checks if a given state allows final output, throws an exception otherwise
     * 
     * @return string
     * 
     * Output is always generated from $this->currentState so there are no parameters
    **/
    private function _validateOutput(): string
    {
        $finalState = $this->statesAvailable->get($this->stateCurrent);
        if($finalState->get('allowFinal') === false) {
            throw (new ErrorException($message = 'The final state is invalid'));
        } else {
            return $finalState->get('output');
        }
    }



}
