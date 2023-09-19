import type Model from "@/Types/Model";
import type Participant from "@/Types/Participant";
import type Competition from "@/Types/Competition";

export default interface CompetitionTeam extends Model {
    name: string;
    points: number;

    participants?: Participant[];

    competition_id: number;
    competition?: Competition;
}
