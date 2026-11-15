import type Competition from "./Competition";
import type Enrollment from "./Enrollment";
import type FinalPrize from "./FinalPrize";
import type Model from "./Model";
import type Product from "./Product";
import type Quest from "./Quest";
import type Speaker from "./Speaker";
import type Sponsor from "./Sponsor";

export default interface Edition extends Model {
    name: string;
    year: number;

    // Relationships
    enrollments?: Enrollment[];
    events?: Event[];
    products?: Product[];
    finalPrizes?: FinalPrize[];
    quests?: Quest[];
    speakers?: Speaker[];
    sponsors?: Sponsor[];
    competitions?: Competition[];
}
