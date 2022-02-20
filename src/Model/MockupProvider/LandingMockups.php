<?php

namespace App\Model\MockupProvider;

use App\Doctrine\GroupType;
use App\Doctrine\SubtripOptionsType;
use App\Doctrine\TripAdditionalInfoType;
use App\Entity\ForumPost;
use App\Entity\ForumThread;
use App\Entity\Group;
use App\Entity\HostingRequest;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Form\DataTransformer\DateTimeTransformer;
use App\Form\InvitationGuest;
use App\Form\InvitationHost;
use App\Form\InvitationType;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Mockery;
use Symfony\Component\Form\FormFactoryInterface;

class LandingMockups implements MockupProviderInterface
{
    private const MOCKUPS = [
        'be visited|none' => [
            'type' => 'template',
            'template' => 'landing/widget/triplegs.html.twig',
        ],
        'be visited|two legs' => [
            'type' => 'template',
            'template' => 'landing/widget/triplegs.html.twig',
        ],
    ];

    public function getFeature(): string
    {
        return 'landing';
    }

    public function getMockups(): array
    {
        return self::MOCKUPS;
    }

    public function getMockupVariables(array $parameters): array
    {
        switch ($parameters['name']) {
            case 'be visited|none':
                return $this->getTripsWidgetEmpty();
            case 'be visited|two legs':
                return $this->getTripsWidgetTwoLegs($parameters['user']);
            default:
                return [];
        }
    }

    public function getMockupParameter(): array
    {
        return [];
    }

    private function getTripsWidgetEmpty(): array
    {
        return [
            'legs' => null,
            'radius' => 10,
        ];
    }

    private function getTripsWidgetTwoLegs(Member $user): array
    {
        $trip = Mockery::mock(Trip::class, [
            'getId' => 1,
            'getCreator' => $user,
            'getSummary' => 'Mocking Bird',
            'getDescription' => 'Mocking description',
            'getCountOfTravellers' => 2,
            'getAdditionalInfo' => TripAdditionalInfoType::NONE,
            'getCreated' => new DateTime(),
        ]);
        $location = Mockery::mock(Location::class, [
            'getId' => 1,
            'getName' => 'Mock',
        ]);
        $leg1 = Mockery::mock(SubTrip::class, [
            'getId' => 1,
            'getArrival' => new Carbon(),
            'getDeparture' => new Carbon(),
            'getOptions' => [SubtripOptionsType::MEET_LOCALS],
            'getLocation' => $location,
            'getTrip' => $trip,
            'getInvitedBy' => $user,
            'getInvitationBy' => null,
        ]);
        $leg2 = Mockery::mock(SubTrip::class, [
            'getId' => 2,
            'getArrival' => new Carbon(),
            'getDeparture' => new Carbon(),
            'getOptions' => [SubtripOptionsType::LOOKING_FOR_HOST],
            'getLocation' => $location,
            'getTrip' => $trip,
            'getInvitedBy' => $user,
            'getInvitationBy' => null,
        ]);

        return [
            'legs' => [
                $leg1,
                $leg2,
            ],
            'radius' => 10,
        ];
    }
}
