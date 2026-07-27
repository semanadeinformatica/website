import type Model from "./Model";
import type { User } from "./User";

export default interface Admin extends Model {
    user_id: number;
    user?: User;
}
