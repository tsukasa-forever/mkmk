<?php


namespace App\Model\Table;


class AtsumarisTable extends AppTable
{
    public function initialize(array $config): void
    {
        parent::initialize($config); // TODO: Change the autogenerated stub
        $this->addBehavior('Timestamp');
    }

    public function create(string $team_id, string $user_id, string $name, string $place, string $description, string $date, string $start_time, string $end_time)
    {
        $atsumari = $this->newEntity([
            'team_id' => $team_id,
            'name' => $name,
            'description' => $description,
            'user_id' => $user_id,
            'place' => $place,
            'date' => new \DateTimeImmutable($date),
            'start_time' => $start_time,
            'end_time' => $end_time
        ]);
        $this->save($atsumari);
        return $atsumari;
    }
}