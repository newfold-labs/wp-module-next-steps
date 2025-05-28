<?php

namespace NewfoldLabs\WP\Module\NextSteps;

/**
 * StepRepository class
 * 
 * This class is responsible for managing the steps in the Next Steps module.
 * It provides methods to get all steps, save all steps, and mark a step as complete.
 */
class StepRepository {
    const OPTION_KEY = 'nfd_next_steps';

    public function get_all(): array {
        $raw = get_option(self::OPTION_KEY, []);
        return array_map([Step::class, 'from_array'], $raw);
    }

    public function save_all(array $steps): void {
        $encoded = array_map(fn($s) => $s->to_array(), $steps);
        update_option(self::OPTION_KEY, $encoded);
    }

    public function mark_complete(string $id): void {
        $steps = $this->get_all();
        foreach ($steps as $step) {
            if ($step->id === $id) {
                $step->status = 'complete';
                break;
            }
        }
        $this->save_all($steps);
    }
}
