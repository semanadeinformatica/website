export default interface User {
    created_at: string;
    current_team_id?: number;
    email_verified_at?: string;
    email: string;
    id: number;
    name: string;
    profile_photo_path?: string;
    profile_photo_url?: string;
    two_factor_confirmed_at?: string;
    two_factor_enabled: boolean;
    updated_at: string;
    usertype_id: number;
    usertype_type: string;
}
