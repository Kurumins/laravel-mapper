<?php

namespace Mapper\Commands;

use Mapper\Customize;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;

class GeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mapper:generate {--t|table= : The name of the table}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate models base on your DB tables';


    /**
     * @var Customize
     */
    protected $customizer;

    /**
     * Create a new command instance.
     * @param Customize $customize
     */
    public function __construct(Customize $customize)
    {
        parent::__construct();

        $this->customizer = $customize;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->comment("Generating models in " . config('mapper.path'));
            $this->customizer->map();
            echo "\n\n\n---ARRRRMAIR.";
            $this->comment("Db mapped");
            $table = new Table($this->output);
            $table->setHeaders(['Table', 'Trait', 'Path']);
            foreach ($this->customizer->getMetaTables() as $tableName => $metaTable) {
                if (!is_null($metaTable->getFullModelName())) {
                    $result = $this->customizer->saveTraitFile($tableName, config('mapper.path'));
                    if ($result === false) {
                        $this->error("$tableName Failed!");
                    } else {
                        $table->addRow([$tableName, $result['trait_name'], $result['path']]);
                    }
                } else {
                    $table->addRow([$tableName, '', '-- No model defined --']);
                }

            }
            $this->customizer->saveMapFile(config('mapper.path_map'));
            $this->comment("Class map save at " . config('mapper.path_map'));
            $table->render();
            $this->info("Success");
        } catch (\Exception $e) {
            $this->error("Sorry: " . $e->getMessage());
            $this->error($e->getFile() . ':' . $e->getLine());
        }
    }


    /**
     * @return string
     */
    protected function getTable()
    {
        return $this->option('table');
    }
}
