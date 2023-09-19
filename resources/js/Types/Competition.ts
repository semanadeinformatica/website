import type Model from "@/Types/Model";
import type Edition from "@/Types/Edition";
import type CompetitionTeam from "@/Types/Competition";

export default interface Competition extends Model {
    theme: string;
    date_start: Date;
    date_end: Date;

    edition_id: number;
    edition?: Edition;

    teams?: CompetitionTeam[];

}
