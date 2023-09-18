import type Enrollment from "./Enrollment";
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
    quests?: Quest[];
    speakers?: Speaker[];
    sponsors?: Sponsor[];
}
