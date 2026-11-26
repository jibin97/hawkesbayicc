<?php

declare(strict_types=1);

namespace App\Forms;

use App\Content;
use InvalidArgumentException;

/**
 * Field definitions for the public forms. Used by both the renderer (views/public/partials/form.php)
 * and the Validator, so what is shown is exactly what is checked.
 *
 * Field keys: name, label, type (text|email|tel|date|number|select|radio|checkboxes|checkbox|textarea),
 * required, max (chars), min/max_num (number), options (list<string>), help, autocomplete, inputmode,
 * width ('half' on wider screens), future (date must be today or later),
 * noun (phrase used in error messages: "Please enter <noun>."), report (label used in alert emails and the admin inbox).
 */
final class Schema
{
    public const FORMS = ['enrolment', 'booking', 'contact'];

    /** @return array<string, mixed> */
    public static function get(string $form): array
    {
        return match ($form) {
            'enrolment' => self::enrolment(),
            'booking'   => self::booking(),
            'contact'   => self::contact(),
            default     => throw new InvalidArgumentException("Unknown form: {$form}"),
        };
    }

    /** @return list<array<string, mixed>> every field of a form, flattened */
    public static function fields(string $form): array
    {
        $fields = [];
        foreach (self::get($form)['sections'] as $section) {
            foreach ($section['fields'] as $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /** @return array<string, mixed> */
    private static function enrolment(): array
    {
        $academy     = Content::get('academy');
        $disciplines = array_map(static fn (array $d): string => $d['name'], $academy['disciplines']);
        $disciplines[] = 'Not sure yet';

        return [
            'type'    => 'enrolment',
            'submit'  => 'Send enrolment',
            'success' => ['title' => 'Thank you, your enrolment has been received',
                          'text'  => "We'll be in touch soon to confirm the class and start date. If it's urgent, call us on 021 1642979."],
            'sections' => [
                ['legend' => 'Student', 'fields' => [
                    ['name' => 'student_name', 'noun' => 'the student\'s full name', 'report' => 'Student name', 'label' => "Student's full name", 'type' => 'text', 'required' => true, 'max' => 100, 'autocomplete' => 'off'],
                    ['name' => 'age_group', 'noun' => 'an age group', 'label' => 'Age group', 'type' => 'select', 'required' => true,
                     'options' => ['Under 5', '5–8 years', '9–12 years', '13–17 years', 'Adult (18+)'], 'width' => 'half'],
                    ['name' => 'experience', 'noun' => 'a dance experience level', 'label' => 'Dance experience', 'type' => 'select', 'required' => true,
                     'options' => ['None: complete beginner', 'Some: less than 2 years', 'Experienced: 2 years or more'], 'width' => 'half'],
                ]],
                ['legend' => 'Class preferences', 'fields' => [
                    ['name' => 'discipline', 'noun' => 'a discipline', 'report' => 'Preferred discipline', 'label' => 'Preferred discipline', 'type' => 'radio', 'required' => true, 'options' => $disciplines],
                    ['name' => 'expectations', 'report' => 'Hoping to get from classes', 'label' => 'What are you hoping to get from classes?', 'type' => 'checkboxes', 'required' => false,
                     'options' => $academy['expectations'], 'help' => 'Choose any that apply.'],
                ]],
                ['legend' => 'Parent / guardian', 'help' => "Adult students: enter your own details.", 'fields' => [
                    ['name' => 'contact_name', 'noun' => 'your full name', 'report' => 'Parent / guardian name', 'label' => 'Full name', 'type' => 'text', 'required' => true, 'max' => 100, 'autocomplete' => 'name'],
                    ['name' => 'email', 'noun' => 'your email address', 'report' => 'Parent / guardian email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'max' => 191, 'autocomplete' => 'email', 'width' => 'half'],
                    ['name' => 'phone', 'noun' => 'a mobile number', 'report' => 'Parent / guardian mobile', 'label' => 'Mobile', 'type' => 'tel', 'required' => true, 'max' => 20, 'autocomplete' => 'tel', 'inputmode' => 'tel', 'width' => 'half'],
                ]],
                ['legend' => 'Emergency contact', 'help' => 'Someone other than the parent / guardian above, if possible.', 'fields' => [
                    ['name' => 'emergency_name', 'report' => 'Emergency contact name', 'label' => 'Name', 'type' => 'text', 'required' => false, 'max' => 100, 'autocomplete' => 'off', 'width' => 'half'],
                    ['name' => 'emergency_phone', 'report' => 'Emergency contact phone', 'label' => 'Phone', 'type' => 'tel', 'required' => false, 'max' => 20, 'autocomplete' => 'off', 'inputmode' => 'tel', 'width' => 'half'],
                ]],
                ['legend' => 'Anything else?', 'fields' => [
                    ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'required' => false, 'max' => 2000,
                     'help' => 'Medical or access needs, preferred days, or questions.'],
                ]],
                ['legend' => 'Consent', 'fields' => [
                    ['name' => 'photo_consent', 'noun' => 'yes or no for photo permission', 'report' => 'Photo permission', 'label' => 'May we use photos of the student to promote HBICC and Krishni Productions events on social media?',
                     'type' => 'radio', 'required' => true, 'options' => ['Yes', 'No']],
                    ['name' => 'terms', 'report' => 'Agreed to terms', 'label' => 'I have read and agree to the HBICC terms and conditions above.', 'type' => 'checkbox', 'required' => true],
                ]],
            ],
            'map' => ['name' => 'contact_name', 'email' => 'email', 'phone' => 'phone', 'message' => 'notes'],
            'subject' => static fn (array $v): string => 'Enrolment: ' . $v['student_name'] . ' (' . $v['discipline'] . ', ' . $v['age_group'] . ')',
        ];
    }

    /** @return array<string, mixed> */
    private static function booking(): array
    {
        return [
            'type'    => 'booking',
            'submit'  => 'Send booking enquiry',
            'success' => ['title' => 'Thank you, your booking enquiry has been sent',
                          'text'  => "We'll check the band's availability and get back to you soon."],
            'sections' => [
                ['legend' => 'Your event', 'fields' => [
                    ['name' => 'event_type', 'noun' => 'the type of event', 'label' => 'Type of event', 'type' => 'select', 'required' => true,
                     'options' => ['Wedding', 'Festival or community event', 'Corporate event', 'Private party', 'Other'], 'width' => 'half'],
                    ['name' => 'event_date', 'noun' => 'the event date', 'label' => 'Event date', 'type' => 'date', 'required' => true, 'future' => true, 'width' => 'half'],
                    ['name' => 'location', 'noun' => 'the venue or town', 'label' => 'Venue or town', 'type' => 'text', 'required' => true, 'max' => 150, 'width' => 'half'],
                    ['name' => 'guests', 'label' => 'Approximate number of guests', 'type' => 'number', 'required' => false, 'min' => 1, 'max_num' => 10000, 'inputmode' => 'numeric', 'width' => 'half'],
                    ['name' => 'message', 'label' => 'Tell us about your event', 'type' => 'textarea', 'required' => false, 'max' => 2000,
                     'help' => 'Timings, set length, music you love, or anything else we should know.'],
                ]],
                ['legend' => 'Your details', 'fields' => [
                    ['name' => 'name', 'noun' => 'your full name', 'label' => 'Full name', 'type' => 'text', 'required' => true, 'max' => 100, 'autocomplete' => 'name', 'width' => 'half'],
                    ['name' => 'organisation', 'label' => 'Organisation (optional)', 'type' => 'text', 'required' => false, 'max' => 150, 'autocomplete' => 'organization', 'width' => 'half'],
                    ['name' => 'email', 'noun' => 'your email address', 'label' => 'Email', 'type' => 'email', 'required' => true, 'max' => 191, 'autocomplete' => 'email', 'width' => 'half'],
                    ['name' => 'phone', 'noun' => 'a phone number', 'label' => 'Phone', 'type' => 'tel', 'required' => true, 'max' => 20, 'autocomplete' => 'tel', 'inputmode' => 'tel', 'width' => 'half'],
                ]],
            ],
            'map' => ['name' => 'name', 'email' => 'email', 'phone' => 'phone', 'message' => 'message'],
            'subject' => static fn (array $v): string => 'Band booking: ' . $v['event_type'] . ' on ' . $v['event_date'] . ' (' . $v['location'] . ')',
        ];
    }

    /** @return array<string, mixed> */
    private static function contact(): array
    {
        return [
            'type'    => 'contact',
            'submit'  => 'Send message',
            'success' => ['title' => 'Thank you, your message has been sent',
                          'text'  => "We'll get back to you as soon as we can."],
            'sections' => [
                ['legend' => null, 'fields' => [
                    ['name' => 'name', 'noun' => 'your full name', 'label' => 'Full name', 'type' => 'text', 'required' => true, 'max' => 100, 'autocomplete' => 'name', 'width' => 'half'],
                    ['name' => 'email', 'noun' => 'your email address', 'label' => 'Email', 'type' => 'email', 'required' => true, 'max' => 191, 'autocomplete' => 'email', 'width' => 'half'],
                    ['name' => 'phone', 'label' => 'Phone (optional)', 'type' => 'tel', 'required' => false, 'max' => 20, 'autocomplete' => 'tel', 'inputmode' => 'tel', 'width' => 'half'],
                    ['name' => 'topic', 'noun' => 'a topic', 'report' => 'Topic', 'label' => 'What is it about?', 'type' => 'select', 'required' => true, 'width' => 'half',
                     'options' => ['Classes & enrolment', 'Tickets & events', 'Booking the band', 'Sponsorship & media', 'Collaboration', 'Something else']],
                    ['name' => 'message', 'noun' => 'your message', 'label' => 'Message', 'type' => 'textarea', 'required' => true, 'max' => 3000],
                ]],
            ],
            'map' => ['name' => 'name', 'email' => 'email', 'phone' => 'phone', 'message' => 'message'],
            'subject' => static fn (array $v): string => 'Contact: ' . $v['topic'],
        ];
    }
}
