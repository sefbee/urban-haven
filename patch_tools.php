<?php

$content = file_get_contents('resources/views/public/partials/showcase-slide.blade.php');
$search = '            </button>
            </div>';
$replace = '            </button>
                <button type="button" x-data
                        @click.prevent.stop="$store.saved.toggle(\'compare\', {{ $property->id }})"
                        :aria-pressed="$store.saved.has(\'compare\', {{ $property->id }}).toString()"
                        :aria-label="$store.saved.has(\'compare\', {{ $property->id }}) ? @js(__(\'Remove :title from compare\', [\'title\' => $property->title])) : @js(__(\'Add :title to compare\', [\'title\' => $property->title]))"
                        aria-label="{{ __(\'Add :title to compare\', [\'title\' => $property->title]) }}"
                        class="uh-icon-action uh-showcase-compare"
                        @if($listingLayout) tabindex="0" @else x-bind:tabindex="slot({{ $index }}) === \'active\' ? 0 : -1" @endif>
                    <i class="fa-solid fa-code-compare" x-show="!$store.saved.has(\'compare\', {{ $property->id }})"></i>
                    <i class="fa-solid fa-code-compare text-brass" x-show="$store.saved.has(\'compare\', {{ $property->id }})" x-cloak></i>
                </button>
            </div>';
$content = str_replace($search, $replace, $content);
file_put_contents('resources/views/public/partials/showcase-slide.blade.php', $content);
