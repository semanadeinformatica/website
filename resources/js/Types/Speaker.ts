import type Model from "./Model";
import type SocialMedia from "./SocialMedia";

export default interface Speaker extends Model {
    title?: string;
    description?: string;
    description_html?: string;
    organization?: string;
    display_name?: string;
    social_media_id?: number;
    social_media?: SocialMedia;
}
