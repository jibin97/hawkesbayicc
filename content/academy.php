<?php

/**
 * Academy & enrolment.
 *
 * CONFIRM: the disciplines list comes from the revamp spec; the old site doesn't list the styles taught.
 * 'timetable' is null until class times, age groups, venue and fees are supplied; the page then shows
 * "timetable being finalised" instead. Format when known:
 *   ['venue' => '', 'fees' => '', 'classes' => [['name' => '', 'ages' => '', 'day' => '', 'time' => ''], …]]
 *
 * 'policies' and 'expectations' are from the old enrolment form (real, current policy).
 */

declare(strict_types=1);

return [
    'disciplines' => [
        [
            'name'    => 'Bharatanatyam',
            'origin'  => 'Classical dance · South India',
            'summary' => 'One of the oldest Indian classical dance forms, known for crisp geometric lines, rhythmic footwork and expressive storytelling through gesture and facial expression.',
        ],
        [
            'name'    => 'Kathak',
            'origin'  => 'Classical dance · North India',
            'summary' => 'A storytelling dance of fast spins, intricate footwork and graceful movement, traditionally performed to live rhythm.',
        ],
        [
            'name'    => 'Odissi',
            'origin'  => 'Classical dance · Odisha, East India',
            'summary' => 'A lyrical style with fluid, sculptural poses inspired by temple art, balancing grace with strong, grounded movement.',
        ],
        [
            'name'    => 'Youth Theatre & Drama',
            'origin'  => 'Workshops',
            'summary' => "Drama workshops for young performers, drawing on HBICC's dance dramas and our long collaboration with Hawke's Bay theatre makers.",
        ],
    ],

    'timetable' => null,

    'expectations' => ['Classical Dance', 'Fitness / Exercise', 'Basic dance moves', 'Advanced dance practice'],

    'policies' => [
        ['title' => 'Fees are paid monthly',  'text' => 'Dance tuition fees are payable one month (4 weeks) in advance.'],
        ['title' => 'Let us know if you miss a class', 'text' => "Please tell your teacher if you can't attend a class; otherwise the fee for that session still applies."],
        ['title' => "One month's notice to leave", 'text' => "We ask for a month's notice if a student is leaving their dance class."],
        ['title' => 'Photos & social media',  'text' => 'With your permission, photos of students may be used to promote HBICC and Krishni Productions events on social media. You choose this on the enrolment form.'],
    ],

    'faqs' => [
        ['q' => 'How do I enrol?',
         'a' => "Fill in the enrolment form on this page. We'll get in touch to confirm the right class and your start date."],
        ['q' => 'Does my child need any dance experience?',
         'a' => "No. Tell us about any previous experience on the form and we'll suggest the right class, whether you're starting with basic dance moves or looking for advanced practice."],   // CONFIRM
        ['q' => 'When and where are classes held?',
         'a' => "Our timetable is being finalised. Call us on 021 1642979 or enrol online and we'll share class times and the venue."],
        ['q' => 'How are fees paid?',
         'a' => 'Tuition fees are paid one month (4 weeks) in advance.'],
        ['q' => 'What happens if we miss a class?',
         'a' => "Please let your teacher know beforehand. If we're not told, the fee for that session is still charged."],
        ['q' => 'How do we stop classes?',
         'a' => "Just give us one month's notice."],
        ['q' => 'Do students get to perform?',
         'a' => 'Yes. Our annual M.A.D.E for a Cause showcase was created as a stage for our students, and raises money for a local charity each year.'],
    ],
];
