import type Model from "@/Types/Model";
import type Student from "@/Types/Student";
import type CompetitionInfo from "@/Types/CompetitionInfo";

export default interface CompetitionTeam extends Model {
    name: string;

    students?: Student[];
    competition?: CompetitionInfo;
}
