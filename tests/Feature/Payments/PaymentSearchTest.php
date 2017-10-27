<?php

namespace Tests\Feature\Payments;

use App\Entities\Payments\Payment;
use App\Entities\Projects\Project;
use Tests\TestCase;

class PaymentSearchTest extends TestCase
{
    /** @test */
    public function user_can_find_payment_by_project_name()
    {
        $admin          = $this->adminUserSigningIn();
        $project        = factory(Project::class)->create(['name' => 'Project']);
        $payment        = factory(Payment::class)->create(['owner_id' => $admin->id, 'project_id' => $project->id]);
        $unShownPayment = factory(Payment::class)->create(['owner_id' => $admin->id]);

        $this->visit(route('payments.index'));
        $this->submitForm(trans('app.search'), [
            'q'          => 'Project',
            'partner_id' => '',
        ]);
        $this->seePageIs(route('payments.index', ['partner_id' => '', 'q' => 'Project']));

        $this->see($payment->project->name);
        $this->dontSee($unShownPayment->project->name);
    }

    /** @test */
    public function partner_find_payment_by_customer_id()
    {
        $admin          = $this->adminUserSigningIn();
        $payment        = factory(Payment::class)->create(['owner_id' => $admin->id]);
        $unShownPayment = factory(Payment::class)->create(['owner_id' => $admin->id]);

        $this->visit(route('payments.index'));
        $this->submitForm(trans('app.search'), [
            'q'          => '',
            'partner_id' => $payment->partner_id,
        ]);
        $this->seePageIs(route('payments.index', ['partner_id' => $payment->partner_id, 'q' => '']));

        $this->see($payment->project->name);
        $this->dontSee($unShownPayment->project->name);
    }
}
