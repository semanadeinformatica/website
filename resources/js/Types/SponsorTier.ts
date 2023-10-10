import type Model from "@/Types/Model";
import type Edition from "./Edition";
import type Sponsor from "./Sponsor";

export default interface SponsorTier extends Model {
    name: string;
    color: string;
    rank: number;

    canSeeCV: boolean;
    canSeeLinkedin: boolean;

    edition_id: number;
    edition?: Edition;

    sponsors?: Sponsor[];
}
