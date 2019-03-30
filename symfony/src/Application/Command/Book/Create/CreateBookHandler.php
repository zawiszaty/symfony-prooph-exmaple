<?php

declare(strict_types=1);

namespace App\Application\Command\Book\Create;

use App\Domain\Author\Exception\AuthorNotFoundException;
use App\Domain\Author\Validator\AuthorValidator;
use App\Domain\Book\Book;
use App\Domain\Book\BookStore;
use App\Domain\Book\ValueObject\Description;
use App\Domain\Category\Exception\CategoryNotExistException;
use App\Domain\Category\Validator\CategoryValidator;
use App\Domain\Common\ValueObject\AggregateRootId;
use App\Domain\Common\ValueObject\Name;
use App\Infrastructure\Common\CommandHandler\CommandHandlerInterface;

class CreateBookHandler implements CommandHandlerInterface
{
    /**
     * @var BookStore
     */
    private $bookStore;

    /**
     * @var CategoryValidator
     */
    private $categoryValidator;

    /**
     * @var AuthorValidator
     */
    private $authorValidator;

    public function __construct(
        BookStore $bookStore,
        CategoryValidator $categoryValidator,
        AuthorValidator $authorValidator
    ) {
        $this->bookStore = $bookStore;
        $this->categoryValidator = $categoryValidator;
        $this->authorValidator = $authorValidator;
    }

    /**
     * @param CreateBookCommand $command
     *
     * @throws CategoryNotExistException
     * @throws AuthorNotFoundException
     */
    public function __invoke(CreateBookCommand $command)
    {
        $this->categoryValidator->exist($command->getCategory());
        $this->authorValidator->exist($command->getAuthor());
        $book = Book::create(
            AggregateRootId::generate(),
            Name::fromString($command->getName()),
            Description::fromString($command->getDescription()),
            $command->getCategory(),
            $command->getAuthor()
        );
        $this->bookStore->save($book);
    }
}
