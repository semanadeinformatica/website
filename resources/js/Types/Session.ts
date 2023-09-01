export default interface Session {
    agent: {
        is_desktop: boolean;
        platform: string;
        browser: string;
    };
    ip_address: string;
    is_current_device: boolean;
    last_active: string;
}
