<?php

$app = require_once __DIR__ . '/../bootstrap.php';

//Include the namespaces of the components we plan to use
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

//Instantiate our Console application
$console = new Application('Reader', '0.1');

//Register a command to run from the command line
//Our command will be started with "./console.php sync"
$console->register('fetch')
        ->setDefinition(array())
        ->setDescription('Fetchs content from all Sources')
        ->setHelp('Usage: <info>./php app/console/fetch.php fetch</info>')
        ->setCode(
                function(InputInterface $input, OutputInterface $output) use ($app) {
                  $output->write("Contacting external data source ...\n");
                  $sources = $app['db']->fetchAll('SELECT * FROM source WHERE enabled = "true"');
                  foreach ($sources as $source) {
                    try {
                      $feed = \Feedtcher\Feedtcher::fectch($source['url']);
                      \Reader\Model\Post::import($source, $feed);

                      $output->write('Imported ' . $feed->title
                              . ' Total :' . count($feed->collection) . "\n");
                    } catch (\Exception $e) {
                      $app['db']->executeQuery('UPDATE source SET fail = (fail + 1) 
                          WHERE id = ?', array($source['id']));

                      $output->write('Fail processing ' . $source['name']
                              . '- ' . $e->getMessage() . "\n");
                    }
                  }
                }
);

$console->register('import')
        ->setDefinition(array(
            new InputArgument('file', InputArgument::REQUIRED, 'Xml file to import'),
            new InputArgument('username', InputArgument::REQUIRED, 'The Username owner of this OMPL'),
        ))
        ->setDescription('Imports OMPL to an User')
        ->setHelp('Usage: <info>./php app/console/fetch.php fetch</info>')
        ->setCode(
                function(InputInterface $input, OutputInterface $output) {
                  $user = \Reader\Model\User::getUser($input->getArgument('username'));

                  if (!$user) {
                    $output->write("Not a valid User\n");
                    return 0;
                  }

                  if (file_exists($input->getArgument('file'))) {
                    $ompl = file_get_contents($input->getArgument('file'));
                    \Reader\Model\Subscription::import($ompl, $user);
                  }
                  else {
                    $output->write("Not a valid file\n");
                  }
                }
);

$console->run();