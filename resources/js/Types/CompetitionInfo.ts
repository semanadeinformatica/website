import type Model from "@/Types/Model";
import type Edition from "@/Types/Edition";
import type CompetitionTeam from "@/Types/CompetitionTeam";

export default interface CompetitionInfo extends Model {
    theme: string;
    date_start: Date;
    date_end: Date;

    edition_id: number;
    edition?: Edition;

    teams?: CompetitionTeam[];

    first_place: number;
    firstPlace?: CompetitionTeam;
    second_place: number;
    secondPlace?: CompetitionTeam;
    third_place: number;
    thirdPlace?: CompetitionTeam;
}
