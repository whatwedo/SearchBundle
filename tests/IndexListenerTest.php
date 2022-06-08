<?php

declare(strict_types=1);

namespace whatwedo\SearchBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use whatwedo\SearchBundle\Entity\Index;
use whatwedo\SearchBundle\Tests\App\Entity\Company;
use whatwedo\SearchBundle\Tests\App\Entity\Contact;
use whatwedo\SearchBundle\Tests\App\Factory\CompanyFactory;
use whatwedo\SearchBundle\Tests\App\Factory\ContactFactory;

class IndexListenerTest extends AbstractIndexTest
{
    public function testEntityCreation()
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        self::assertSame(0, $em->getRepository(Index::class)->count([]));

        /** @var Contact $contact */
        ContactFactory::createOne([
            'name' => 'Maurizio Monticelli',
            'company' => CompanyFactory::createOne([
                'name' => 'whatwedo GmbH',
                'city' => 'Bern',
                'country' => 'Switzerland',
                'taxIdentificationNumber' => '12344566',
            ]),
        ])->object();

        $indexResults = $em->getRepository(Index::class)->findAll();
        self::assertSame(6, count($indexResults));

        /** @var Index $indexResult */
        foreach ($indexResults as $indexResult) {
            if ($indexResult->getModel() === Company::class) {
                $value = null;
                switch ($indexResult->getGroup()) {
                    case 'default':
                        $value = 'whatwedo GmbH dummy Switzerland 12344566';
                        break;
                    case 'global':
                    case 'company':
                        $value = 'whatwedo GmbH';
                        break;
                }

                self::assertSame($value, $indexResult->getContent(), 'test on group ' . $indexResult->getGroup() . ' failed');
            }
        }
    }

    public function testEntityUpdate()
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        /** @var Contact $contact */
        $contact = ContactFactory::createOne([
            'name' => 'Maurizio Monticelli',
            'company' => CompanyFactory::createOne([
                'name' => 'whatwedo GmbH',
                'city' => 'Bern',
                'country' => 'Switzerland',
                'taxIdentificationNumber' => '12344566',
            ]),
        ])->object();

        $contactId = $contact->getId();

        $em->clear();

        $contact = $em->getRepository(Contact::class)->find($contactId);

        $contact->getCompany()->setName('company');
        $contact->getCompany()->setCity('city');
        $contact->getCompany()->setCountry('county');
        $contact->getCompany()->setTaxIdentificationNumber('123456');

        $em->flush();
        $em->clear();

        $indexResults = $em->getRepository(Index::class)->findAll();

        /** @var Index $indexResult */
        foreach ($indexResults as $indexResult) {
            if ($indexResult->getModel() === Company::class) {
                $value = null;
                switch ($indexResult->getGroup()) {
                    case 'default':
                        $value = 'company dummy county 123456';
                        break;
                    case 'global':
                    case 'company':
                        $value = 'company';
                        break;
                }

                self::assertSame($value, $indexResult->getContent(), 'test on group ' . $indexResult->getGroup() . ' failed');
            }
        }
    }

    public function testEntityDelete()
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        /** @var Contact $contact */
        $contact = ContactFactory::createOne()->object();

        $contactId = $contact->getId();

        $em->clear();

        $contact = $em->getRepository(Contact::class)->find($contactId);

        $contact->getCompany()->setName('company');
        $contact->getCompany()->setCity('city');
        $contact->getCompany()->setCountry('county');
        $contact->getCompany()->setTaxIdentificationNumber('123456');

        $em->flush();
        $em->clear();

        $indexResults = $em->getRepository(Index::class)->findAll();

        self::assertCount(6, $indexResults);

        $contact = $em->getRepository(Contact::class)->find($contactId);
        $em->remove($contact);
        $em->remove($contact->getCompany());
        $em->flush();

        $indexResults = $em->getRepository(Index::class)->findAll();

        self::assertCount(0, $indexResults);
    }
}
