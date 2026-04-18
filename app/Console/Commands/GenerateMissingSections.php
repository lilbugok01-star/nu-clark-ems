<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateMissingSections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-missing-sections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $courses = \App\Models\Course::all();
        $count = 0;

        foreach ($courses as $course) {
            $hasSections = \App\Models\Section::where('course_id', $course->id)->exists();

            if (!$hasSections) {
                $this->info("Generating sections for {$course->code}...");
                
                $prefix = preg_replace('/[^A-Z]/', '', strtoupper($course->code));
                if (strlen($prefix) > 3) {
                    $prefix = substr($prefix, -3);
                } elseif (strlen($prefix) == 0) {
                    $prefix = 'SEC';
                }

                for ($year = 1; $year <= 4; $year++) {
                    for ($sec = 1; $sec <= 5; $sec++) {
                        $sectionName = $prefix . '-' . $year . str_pad($sec, 2, '0', STR_PAD_LEFT);
                        \App\Models\Section::firstOrCreate(
                            ['course_id' => $course->id, 'name' => $sectionName],
                            ['year_level' => $year, 'is_active' => true]
                        );
                        $count++;
                    }
                }
            }
        }

        $this->info("Successfully generated {$count} missing sections.");
    }
}
