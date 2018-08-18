<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dallgoot\Yaml as Y;

$folder = 'definitions';

$classStart = <<<'EOF'
<?php

namespace Dallgoot\Yaml\tests;

class %s extends BaseTest
{
    public function setUp()
    {
        parent::init(__DIR__.'./definitions/%s');
    }

EOF;

$classEnd = "\n}";

$funcDefStart = <<<'EOF'

    public function test_%s()
    {

EOF;
$funcDefEnd = <<<'EOF'

    }

EOF;
$expectParsingTemplate    = '        $this->test_expectParsing("%s", $this->source->{"%s"});';
$expectParseErrorTemplate = '        $this->test_expectParseError(file_get_contents("%s"), $this->source->{"%s"});';
$expectDumpingTemplate    = '        $this->test_expectDumping("%s", $this->source->{"%s"});';

$fileUseMethods = ['dumping'  => $expectDumpingTemplate,
                   'examples' => $expectParsingTemplate,
                   'failing'  => $expectParseErrorTemplate,
                   'parsing'  => $expectParsingTemplate];

foreach (glob( __DIR__."/$folder/*.yml", GLOB_ERR) as $fileName) {
    $name      = str_replace('.yml', '', basename($fileName));
    $classFile = ucfirst($name).'.php';
    echo "\nGenerating class '$classFile'\n";
    $fileHandle = fopen(__DIR__.'/'.$classFile, 'w');
    // writing start of the class description
    fwrite($fileHandle, sprintf($classStart, ucfirst($name), basename($fileName)));
    $testsObject    = Y::parseFile($fileName);
    $methodTemplate = $funcDefStart.$fileUseMethods[strstr($name, '_', true)].$funcDefEnd;
    //for each tests write the corresponding method
    foreach (get_object_vars($testsObject) as $methodName=>$testProp) {
        echo "    $methodName\n";
        fwrite($fileHandle, sprintf($methodTemplate, ucfirst($methodName), "$methodName.yml", $methodName));
    }
    fwrite($fileHandle, $classEnd);
    fclose($fileHandle);
}