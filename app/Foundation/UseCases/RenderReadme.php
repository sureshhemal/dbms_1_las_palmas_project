<?php

namespace App\Foundation\UseCases;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;

final class RenderReadme
{
    public function execute(): string
    {
        return Str::markdown(
            File::get(base_path('README.md')),
            [
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
                'heading_permalink' => [
                    'apply_id_to_heading' => true,
                    'id_prefix' => '',
                    'fragment_prefix' => '',
                    'insert' => 'none',
                ],
                'external_link' => [
                    'internal_hosts' => [],
                    'open_in_new_window' => true,
                    'noopener' => 'external',
                    'noreferrer' => 'external',
                ],
            ],
            [new HeadingPermalinkExtension, new ExternalLinkExtension],
        );
    }
}
