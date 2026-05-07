<?php
declare(strict_types=1);

namespace Iranimij\Badger\Test\Unit;

use PHPUnit\Framework\TestCase;

class ModuleRegistrationTest extends TestCase
{
    private string $moduleRoot;

    protected function setUp(): void
    {
        $this->moduleRoot = dirname(__DIR__, 2);
    }

    public function testModuleXmlDeclaresBadgerName(): void
    {
        $xml = $this->loadXml('etc/module.xml');
        self::assertSame('Iranimij_Badger', (string) $xml->module['name']);
    }

    public function testModuleXmlSequenceIncludesCore(): void
    {
        $xml = $this->loadXml('etc/module.xml');
        $deps = [];
        foreach ($xml->module->sequence->module as $dep) {
            $deps[] = (string) $dep['name'];
        }
        self::assertContains('Iranimij_Core', $deps);
        self::assertContains('Magento_Catalog', $deps);
        self::assertContains('Magento_Ui', $deps);
    }

    public function testAclResourcesUseBadgerVocabulary(): void
    {
        $xml = $this->loadXml('etc/acl.xml');
        $xml->registerXPathNamespace('', '');
        $ids = [];
        foreach ($xml->xpath('//resource') as $resource) {
            $ids[] = (string) $resource['id'];
        }
        self::assertContains('Iranimij_Badger::badger', $ids);
        self::assertContains('Iranimij_Badger::badger_manage', $ids);
        self::assertContains('Iranimij_Badger::badger_config', $ids);

        foreach ($ids as $id) {
            self::assertStringNotContainsStringIgnoringCase('amasty', $id);
            self::assertStringNotContainsStringIgnoringCase('label', $id);
        }
    }

    public function testRouteUsesBadgerFrontName(): void
    {
        $xml = $this->loadXml('etc/adminhtml/routes.xml');
        $route = $xml->router->route;
        self::assertSame('badger', (string) $route['id']);
        self::assertSame('badger', (string) $route['frontName']);
    }

    public function testMenuIdsUseBadgerVocabulary(): void
    {
        $xml = $this->loadXml('etc/adminhtml/menu.xml');
        $ids = [];
        foreach ($xml->menu->add as $node) {
            $ids[] = (string) $node['id'];
        }
        foreach ($ids as $id) {
            self::assertStringStartsWith('Iranimij_Badger::menu_', $id);
            self::assertStringNotContainsStringIgnoringCase('label', $id);
            self::assertStringNotContainsStringIgnoringCase('amasty', $id);
        }
    }

    public function testSystemConfigSectionUsesBadgerId(): void
    {
        $xml = $this->loadXml('etc/adminhtml/system.xml');
        self::assertSame('iranimij_badger', (string) $xml->system->section['id']);
    }

    public function testRegistrationRegistersBadgerModule(): void
    {
        $contents = (string) file_get_contents($this->moduleRoot . '/registration.php');
        self::assertStringContainsString('Iranimij_Badger', $contents);
        self::assertStringContainsString('ComponentRegistrar::MODULE', $contents);
    }

    public function testNoAmastyReferenceAnywhere(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->moduleRoot, \FilesystemIterator::SKIP_DOTS)
        );
        $matches = [];
        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }
            if (str_contains($file->getPathname(), '/Test/Unit/')) {
                continue;
            }
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, ['php', 'xml', 'phtml', 'json', 'csv', 'js', 'less', 'css'], true)) {
                continue;
            }
            $haystack = strtolower((string) file_get_contents($file->getPathname()));
            if (str_contains($haystack, 'amasty')) {
                $matches[] = $file->getPathname();
            }
        }
        self::assertSame([], $matches, 'Files contain "amasty" string.');
    }

    private function loadXml(string $relativePath): \SimpleXMLElement
    {
        $path = $this->moduleRoot . '/' . $relativePath;
        self::assertFileExists($path);
        $xml = simplexml_load_file($path);
        self::assertInstanceOf(\SimpleXMLElement::class, $xml);
        return $xml;
    }
}
