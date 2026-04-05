<?php

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\ValueObjects\Cpf;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CpfTest extends TestCase
{
    public function test_should_validate_valid_cpf(): void
    {
        $this->assertTrue(Cpf::isValid('52998224725'));
    }

    public function test_should_reject_all_same_digits(): void
    {
        $this->assertFalse(Cpf::isValid('11111111111'));
        $this->assertFalse(Cpf::isValid('00000000000'));
        $this->assertFalse(Cpf::isValid('99999999999'));
    }

    public function test_should_reject_wrong_length(): void
    {
        $this->assertFalse(Cpf::isValid('123'));
        $this->assertFalse(Cpf::isValid('123456789012'));
    }

    public function test_should_reject_invalid_check_digits(): void
    {
        $this->assertFalse(Cpf::isValid('52998224720'));
    }

    public function test_should_validate_cpf_with_mask(): void
    {
        $this->assertTrue(Cpf::isValid('529.982.247-25'));
    }

    public function test_should_sanitize_removing_non_digits(): void
    {
        $this->assertEquals('52998224725', Cpf::sanitize('529.982.247-25'));
        $this->assertEquals('52998224725', Cpf::sanitize('52998224725'));
    }

    public function test_constructor_should_accept_valid_cpf(): void
    {
        $cpf = new Cpf('529.982.247-25');

        $this->assertEquals('52998224725', $cpf->value);
        $this->assertEquals('52998224725', (string) $cpf);
    }

    public function test_constructor_should_throw_for_invalid_cpf(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Cpf('11111111111');
    }
}
