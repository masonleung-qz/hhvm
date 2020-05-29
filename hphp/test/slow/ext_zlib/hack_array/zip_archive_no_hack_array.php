<?hh

const string ZIP_NAME = 'output.zip';
const string OUTPUT_DIR = 'output';
const varray<string> TARGETS = varray['bing', 'bong'];
const OPTS = darray[
  'add_path' => 1,
  'remove_all_path' => 1,
  'remove_path' => 1,
];

function stat_archive(ZipArchive $zip): void {
  var_dump($zip->numFiles);
  for ($i = 0; $i < $zip->numFiles; $i++) {
    var_dump($zip->statIndex($i));
  }
}

function stat_targets(ZipArchive $zip): void {
  foreach (TARGETS as $target) {
    var_dump($zip->statName($target));
  }
}

<<__EntryPoint>>
function main() {
  try {
    $zip = new ZipArchive();
    $zip->open(getenv('HPHP_TEST_TMPDIR') . ZIP_NAME, ZipArchive::CREATE);
    $zip->addGlob(__DIR__ . '/test_files/foo*', 0, OPTS);
    $zip->addPattern('/b[io]ng/', __DIR__ . '/test_files', OPTS);
    $zip->close();
    $zip->open(getenv('HPHP_TEST_TMPDIR') . ZIP_NAME);
    stat_archive($zip);
    stat_targets($zip);
    $zip->extractTo(getenv('HPHP_TEST_TMPDIR') . OUTPUT_DIR, TARGETS);
    var_dump(scandir(getenv('HPHP_TEST_TMPDIR') . OUTPUT_DIR));
  } finally {
    // Cleanup
    foreach (TARGETS as $target) {
      unlink(getenv('HPHP_TEST_TMPDIR') . OUTPUT_DIR . "/$target");
    }
    rmdir(getenv('HPHP_TEST_TMPDIR') . OUTPUT_DIR);
    unlink(getenv('HPHP_TEST_TMPDIR') . ZIP_NAME);
  }
}
