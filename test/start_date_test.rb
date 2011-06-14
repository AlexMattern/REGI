#! /usr/bin/ruby


########################################
# U S E R      C O N F I G


# This will use Watir to test web pages
require "rubygems"
require "watir"
require 'test/unit'




def relax
    #sleep 0.5
end


class Standard
    attr_reader :b, :start_date, :end_date, :event_name, :description
    attr_accessor :event_id
    def initialize
        @start_date = "2011-01-01"
        @end_date = "2011-01-01"
        @event_name = "The Name I was Born With"
        @description = "A short, concise treatise on what you will get out of this."
        @event_id = ''
        Watir::Browser.default="firefox"
        puts "Opening a Browser"
        @b = Watir::Browser.new
    end

        def login
        ########### U S E R    D E F I N E D  ######################
        test_site = "localhost/regi/login.php"
        test_user = "jacktest"
        test_pass = "jacktest"
        #############################################################
        puts "Opening Test Site"
        @b.goto test_site
        puts "Entering User Name"
        # Note: ".set" is really slow, so I'm using ".value=" instead
        @b.text_field(:name, "user_name").value = test_user
        puts "Entering Password"
        @b.text_field(:name, "user_password").value = test_pass
        puts "Logging In"
        @b.button(:value, "login").click
        @b   # return value
    end
    def create
        # ADD NEW EVENT
        puts "Clicking Create New Event"
        @b.link(:text, "Create New Event").click
        puts "Entering Information"
        @b.text_field(:name, "start_date").value = start_date
        @b.text_field(:name, "event_name").value = event_name
        @b.text_field(:name, "description").value = description
        @b.button(:value, "New Event").click
    end
end

class AMC < Test::Unit::TestCase
    def setup
        @truck = Standard.new
        @truck.login
    end
    #~ def test_00_login
        #~ assert(@truck.b.text.include?( "you are now logged in!" ), "Not Logged In")
        #~ puts "Success!"
        #~ relax
        #~ @truck.b.close
        #~ relax
    #~ end
    def test_01_create_event_valid_start_no_end
        puts "Clicking Create New Event"
        @truck.b.link(:text, "Create New Event").click
        puts "Entering Information"
        @truck.b.text_field(:name, "start_date").value = @truck.start_date
        @truck.b.text_field(:name, "event_name").value = @truck.event_name
        @truck.b.text_field(:name, "description").value = @truck.description
        @truck.b.button(:value, "New Event").click
        assert(@truck.b.text.include?( "This event has been inserted into the database"), "Event not inserted")
        assert(@truck.b.html.include?( @truck.start_date ),"Start date not found on page")
        assert(@truck.b.html.include?( @truck.event_name ),"Event name not found on page")
        assert(@truck.b.html.include?( @truck.description ),"Description not found on page")
        # Change Start Date to another reasonable value
        new_date = "2011-02-28"
        @truck.b.text_field(:name, "start_date").value = new_date
        @truck.b.button(:value, "Update Event").click
        assert(@truck.b.text.include?( "This event has been updated in the database"), "Event not inserted")
        assert(@truck.b.html.include?( new_date ),"Start date not found on page")

        @truck.b.close

    end

end

        #~ truck.start_date = "2011-12-24"
        #~ event_name = "test_event"
        #~ description= "description " * 24