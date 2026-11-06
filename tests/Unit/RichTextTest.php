<?php

use App\Domain\Shared\Support\RichText;

it('keeps safe formatting', function () {
    expect(RichText::clean('<h2>المواصفات</h2><p><strong>سماكة</strong> 2 ملم</p><ul><li>أ</li></ul>'))
        ->toBe('<h2>المواصفات</h2><p><strong>سماكة</strong> 2 ملم</p><ul><li>أ</li></ul>');
});

it('strips scripts, event handlers and dangerous links', function () {
    $clean = RichText::clean('<p onclick="alert(1)">x</p><script>alert(1)</script><a href="javascript:alert(1)">y</a><img src=x onerror=alert(1)>');

    expect($clean)->not->toContain('script')
        ->not->toContain('onclick')
        ->not->toContain('javascript:')
        ->not->toContain('onerror')
        ->not->toContain('<img');
});

it('treats empty editor output as no content', function () {
    expect(RichText::clean('<p></p>'))->toBeNull()
        ->and(RichText::clean(null))->toBeNull()
        ->and(RichText::clean('<p> </p>'))->toBeNull();
});

it('marks outbound links as nofollow', function () {
    expect(RichText::clean('<a href="https://example.com">x</a>'))->toContain('rel="noopener nofollow"');
});
