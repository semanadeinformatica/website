import type Model from "./Model";
import type SocialMedia from "./SocialMedia";

export default interface Speaker extends Model {
    name: string;
    title?: string;
    description?: string;
    organization?: string;
    social_media_id?: number;
    social_media?: SocialMedia;
    event_id: number;
}
