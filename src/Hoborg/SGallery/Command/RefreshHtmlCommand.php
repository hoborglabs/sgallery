<?php
namespace Hoborg\SGallery\Command;

use Symfony\Component\Console\Command\Command,
	Symfony\Component\Console\Input\InputArgument,
	Symfony\Component\Console\Input\InputInterface,
	Symfony\Component\Console\Input\InputOption,
	Symfony\Component\Console\Output\OutputInterface;

class RefreshHtmlCommand extends Command {

	protected $photoExtensions = array('jpg', 'jpeg', 'png', 'gif');

	protected function configure() {
		$this->setName('refresh:html')
			->setDescription('Refresh gallery HTML.');

		$this->m = new \Mustache_Engine(array('charset' => 'UTF-8'));
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$config = $this->getApplication()->getConfiguration();

		// check source and target folders
		$this->check($config);

		$output->writeln("<info>Refresh HTML Files.</info>");
		$output->writeln("scanning {$config['source']}");
		$folders = $this->scanSourceForFolders($config['source']);
		$this->processFolders($folders);
	}

	protected function scanSourceForFolders($folderPath) {
		$dir = scandir($folderPath);
		$config = $this->getApplication()->getConfiguration();
		$folder = array(
			'name' => strtolower(basename($folderPath)),
			'path' => $folderPath,
			'slug' => '',
			'meta' => '',
			'cover' => '',
			'folders' => array(),
		);

		// get relative path for html
		$slug = str_replace($config['source'], '', $folder['path']);
		$slug = strtolower(str_replace(' ', '-', $slug));
		$slug = preg_replace('/-+/', '-', $slug);
		$slug = preg_replace('/[^a-zA-Z0-9\/\-_]/', '', $slug);
		$folder['slug'] = $slug;

		$folder['cover'] = '/static/thumbnails/' . md5($folderPath) . "-cvr.jpg";
		foreach ($dir as $entry) {
			// skip . .. and any file/folder that starts with "."
			if (0 === strpos($entry, '.')) {
				continue;
			}

			if (is_dir("{$folderPath}/{$entry}")) {
				$folder['folders'][] = $this->scanSourceForFolders("{$folderPath}/{$entry}");
				continue;
			}

			// get meta
		}

		$folder = $this->findCovers($folder);

		return $folder;
	}

	protected function findCovers(array $folder) {
		foreach ($folder['folders'] as $i => $subfolder) {
			$folder['folders'][$i] = $this->findCovers($subfolder);
		}

		if (empty($folder['cover'])) {
			if (!empty($folder['folders'])) {
				$folder['cover'] = $folder['folders'][0]['cover'];
			}
		}

		return $folder;
	}

	protected function processFolders(array $folder) {
		$this->generateAlbum($folder);
		foreach ($folder['folders'] as $subFolder) {
			$this->processFolders($subFolder);
		}
	}

	protected function generateAlbum(array $folder) {
		$config = $this->getApplication()->getConfiguration();
		$albumRoot = $config['target'] . '/albums/' . $folder['slug'];

		if (!is_dir($albumRoot)) {
			mkdir($albumRoot, 0755, 1);
		}

		$tempalteRoot = $this->getApplication()->getAppRoot() . '/templates/' . $config['skin'];
		$albumHtml = $this->mustacheRender("{$tempalteRoot}/album.html", $this->getAlbumData($folder));
		$pageHtml = $this->mustacheRender("{$tempalteRoot}/page.html", $this->getPageData($folder, $albumHtml));

		file_put_contents("{$albumRoot}/index.html", $pageHtml);
	}

	protected function getAlbumData(array $folder) {
		$album = array();
		$config = $this->getApplication()->getConfiguration();

		$parts = explode('/', $folder['slug']);
		$slugs = array();
		if (!empty($parts)) {
			$slug = '';
			$slugs[] = array(
				'href' => '/',
				'text' => $config['i18n']['nav.home']
			);
			foreach ($parts as $part) {
				if (empty($part)) {
					continue;
				}
				$slug .= "/{$part}";
				$slugs[] = array(
					'href' => $slug,
					'text' => $part,
				);
			}

			$last = array_pop($slugs);
			unset($last['href']);
			$slugs[] = $last;
		}

		$album = array(
			'i18n' => $config['i18n'],
			'name' => $folder['name'],
			'albums' => $folder['folders'],
			'has_albums' => !empty($folder['folders']),
			'slugs' => $slugs,
		);

		return $album;
	}

	protected function getPageData(array $folder, $body) {
		$config = $this->getApplication()->getConfiguration();
		$pageConfig = array(
			'photos' => array(),
		);
		$page = array(
			'body' => $body,
			'page' => array(
				'title' => $folder['name'],
				'config' => '',
			),
		);

		$i = 0;
		$jsonFileName = '/static/json/' . md5($folder['path']) . '-000000.json';
		while (is_readable($config['target'] . $jsonFileName)) {
			$pageConfig['photos'][] = $jsonFileName;
			$jsonFileName = '/static/json/' . md5($folder['path']) . '-' . str_pad(++$i, 6, '0', STR_PAD_LEFT) . '.json';
		}
		$page['page']['config'] = json_encode($pageConfig);

		return $page;
	}

	protected function mustacheRender($template, array $data) {
		return $this->m->render(file_get_contents($template), $data);
	}

	protected function check(array $config) {
		if (!is_readable($config['source'])) {
			throw new \Exception('Source folder not readable', 1);
		}
		if (!is_readable($config['target'])) {
			throw new \Exception('Target folder not readable', 1);
		}
		if (!is_writable($config['target'])) {
			throw new \Exception('Target folder is not writable', 1);
		}
	}

}
