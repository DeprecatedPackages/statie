<?php declare(strict_types=1);

namespace Symplify\Statie\Renderable;

use SplFileInfo;
use Symplify\Statie\Configuration\Configuration;
use Symplify\Statie\Contract\Renderable\FileDecoratorInterface;
use Symplify\Statie\Output\FileSystemWriter;
use Symplify\Statie\Renderable\File\FileFactory;

final class RenderableFilesProcessor
{
    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var FileSystemWriter
     */
    private $fileSystemWriter;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var FileDecoratorInterface[]
     */
    private $fileDecorators = [];

    public function __construct(
        FileFactory $fileFactory,
        FileSystemWriter $fileSystemWriter,
        Configuration $configuration
    ) {
        $this->fileFactory = $fileFactory;
        $this->fileSystemWriter = $fileSystemWriter;
        $this->configuration = $configuration;
    }

    public function addFileDecorator(FileDecoratorInterface $fileDecorator): void
    {
        $this->fileDecorators[] = $fileDecorator;
    }

    /**
     * @param SplFileInfo[] $fileInfos
     */
    public function processFiles(array $fileInfos): void
    {
        if (! count($fileInfos)) {
            return;
        }

        $files = $this->fileFactory->createFromFileInfos($fileInfos);

        foreach ($this->getFileDecorators() as $fileDecorator) {
            $files = $fileDecorator->decorateFiles($files);
        }

        $this->fileSystemWriter->copyRenderableFiles($files);
    }

    /**
     * @return FileDecoratorInterface[]
     */
    private function getFileDecorators(): array
    {
        $this->sortFileDecorators();

        return $this->fileDecorators;
    }

    private function sortFileDecorators(): void
    {
        usort($this->fileDecorators, function (FileDecoratorInterface $first, FileDecoratorInterface $second) {
            return $first->getPriority() < $second->getPriority();
        });
    }
}
